<?php

namespace trntv\bus;

use Symfony\Component\Process\Process;
use trntv\bus\base\interfaces\BackgroundCommand;
use trntv\bus\base\interfaces\Command;
use trntv\bus\base\interfaces\Handler;
use trntv\bus\base\interfaces\Middleware;
use trntv\bus\base\interfaces\QueuedCommand;
use trntv\bus\base\interfaces\SelfHandlingCommand;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use trntv\bus\exceptions\MissingHandlerException;


/**
 * Class CommandBus
 * @package trntv\bus
 */
class CommandBus extends Component
{
    /**
     * @var string
     */
    public $queue = 'queue';
    /**
     * @var array
     */
    public $serializer = ['serialize', 'unserialize'];
    /**
     * @var string Path to php executable
     */
    public $backgroundHandlerBinary;
    /**
     * @var string Path to cli script
     */
    public $backgroundHandlerPath;
    /**
     * @var string console route
     */
    public $backgroundHandlerRoute;
    /**
     * @var array
     */
    public $backgroundHandlerArguments = [];

    /**
     * @var array HandlerInterface[]
     */
    protected $handlers = [];
    /**
     * @var array MiddlewareInterface[]
     */
    protected $middlewares = [];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->queue = Instance::ensure($this->queue, 'yii\queue\QueueInterface');

        parent::init();
    }

    /**
     * @param Command $command
     * @return mixed
     */
    public function handle(Command $command)
    {
        if ($command instanceof QueuedCommand && !$command->isRunningInQueue()) {
            return $this->handleInQueue($command);
        } else if ($command instanceof BackgroundCommand && !$command->isRunningInBackground()) {
            return $this->handleInBackground($command);
        }
        return $this->handleNow($command);
    }

    /**
     * @param Command $command
     * @return mixed
     */
    public function handleNow(Command $command)
    {
        $chain = $this->createMiddlewareChain($command, $this->middlewares);
        return $chain($command);

    }

    /**
     * @param $command
     * @param array $middlewareList
     * @return \Closure
     * @throws InvalidConfigException
     */
    protected function createMiddlewareChain($command, array $middlewareList) {

        $lastCallable = $this->createHandlerMiddleware($command);

        while ($middleware = array_pop($middlewareList)) {
            if (!$middleware instanceof Middleware) {
                throw new InvalidConfigException;
            }
            $lastCallable = function ($command) use ($middleware, $lastCallable) {
                return $middleware->execute($command, $lastCallable);
            };
        }
        return $lastCallable;
    }

    /**
     * @param Command $command
     * @return \Closure
     * @throws MissingHandlerException
     */
    protected function createHandlerMiddleware(Command $command)
    {
        $type = get_class($command);

        if ($command instanceof SelfHandlingCommand) {
            $handlerMiddleware = function ($command) {
                return $command->handle();
            };
        } elseif (array_key_exists($type, $this->handlers)) {
            $handler = $this->handlers[$type];
            $handlerMiddleware = function ($command) use ($handler) {
                return $handler->handle($command);
            };
        } else {
            throw new MissingHandlerException('Handler not found');
        }

        return $handlerMiddleware;
    }

    /**
     * @param BackgroundCommand $command
     * @return string
     */
    protected function handleInBackground(BackgroundCommand $command)
    {
        $binary = $this->getConsoleHandlerBinary();
        $path = $this->getBackgroundHandlerPath();
        $route = $this->getBackgroundHandlerRoute();
        $arguments = implode(' ', $this->getBackgroundHandlerArguments($command));

        $process = new Process("{$binary} {$path} {$route} {$arguments}");

        if (!$command->isAsync()) {
            $process->run();
        } else {
            $process->start();
        }

        return $process;
    }

    /**
     * @param QueuedCommand $command
     * @return mixed
     */
    public function handleInQueue(QueuedCommand $command)
    {
        return $this->queue->push(
            [
                'serializer' => $this->serializer,
                'object' => $this->serialize($command)
            ],
            $command->getQueueName()
        );
    }

    /**
     * @return bool|string
     */
    public function getConsoleHandlerBinary()
    {
        $binary = $this->backgroundHandlerBinary ?: PHP_BINARY;
        return Yii::getAlias($binary);
    }

    /**
     * @return bool|string
     */
    public function getBackgroundHandlerPath()
    {
        return Yii::getAlias($this->backgroundHandlerPath);
    }

    /**
     * @return mixed
     */
    public function getBackgroundHandlerRoute()
    {
        return $this->backgroundHandlerRoute;
    }

    /**
     * @param $command
     * @return array
     */
    private function getBackgroundHandlerArguments($command)
    {
        $arguments = $this->backgroundHandlerArguments;
        $command = $this->serializeBackgroundCommand($command);
        array_unshift($arguments, $command);
        return $arguments;
    }

    /**
     * @param $command
     * @return mixed
     */
    public function serialize($command)
    {
        return call_user_func($this->serializer[0], $command);
    }

    /**
     * @param $command
     * @return mixed
     */
    public function unserialize($command)
    {
        return call_user_func($this->serializer[1], $command);
    }

    /**
     * @param $command
     * @return string
     */
    public function serializeBackgroundCommand($command)
    {
        return base64_encode($this->serialize($command));
    }

    /**
     * @param $command
     * @return mixed
     */
    public function unserializeBackgroundCommand($command)
    {
        return $this->unserialize(base64_decode($command));
    }

    /**
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * @param array $handlers
     */
    public function setHandlers($handlers)
    {
        foreach ($handlers as $k => $handler) {
            $this->handlers[$k] = Instance::ensure($handler, Handler::class);
        }
    }

    /**
     * @return array
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * @param array $middlewares
     */
    public function setMiddlewares($middlewares)
    {
        foreach ($middlewares as $k => $middleware) {
            $this->middlewares[$k] = Instance::ensure($middleware, Middleware::class);
        }
    }

    public function addMiddleware(Middleware $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    public function addHandler(Handler $handler, $type)
    {
        $this->handlers[$type] = $handler;
    }
}
