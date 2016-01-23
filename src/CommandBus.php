<?php

namespace trntv\bus;

use Symfony\Component\Process\Process;
use trntv\bus\interfaces\BackgroundCommand;
use trntv\bus\interfaces\Command;
use trntv\bus\interfaces\HandlerLocator;
use trntv\bus\interfaces\Middleware;
use trntv\bus\interfaces\QueuedCommand;
use trntv\bus\interfaces\SelfHandlingCommand;
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
     * @var HandlerLocator|null
     */
    public $locator;
    /**
     * @var Middleware[]
     */
    protected $middlewares = [];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        if ($this->queue) {
            $this->queue = Instance::ensure($this->queue, 'yii\queue\QueueInterface');
        }
        if ($this->locator) {
            $this->locator = Instance::ensure($this->locator, 'trntv\bus\interfaces\HandlerLocator');
        }
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
     * @param BackgroundCommand $command
     * @return string
     */
    protected function handleInBackground(BackgroundCommand $command)
    {
        $binary = $this->getBackgroundHandlerBinary();
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
     * @return bool|string
     */
    public function getBackgroundHandlerBinary()
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
        if ($command instanceof SelfHandlingCommand) {
            $handlerMiddleware = function ($command) {
                return $command->handle();
            };
        } else {
            $handler = $this->locator->locate($command, $this);

            if (!$handler) {
                throw new MissingHandlerException('Handler not found');
            }

            $handlerMiddleware = function ($command) use ($handler) {
                return $handler->handle($command);
            };
        }

        return $handlerMiddleware;
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

    /**
     * @param Middleware $middleware
     */
    public function addMiddleware(Middleware $middleware)
    {
        $this->middlewares[] = $middleware;
    }
}
