<?php

namespace trntv\bus\middlewares;

use trntv\bus\interfaces\Middleware;
use trntv\bus\interfaces\QueuedCommand;
use yii\base\Object;
use yii\di\Instance;
use yii\queue\QueueInterface;

/**
 * Class QueuedCommandMiddleware
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
class QueuedCommandMiddleware extends Object implements Middleware
{
    /**
     * @var mixed|QueueInterface
     */
    public $queue = 'queue';

    /**
     * @var array
     */
    public $serializer = ['serialize', 'unserialize'];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->queue = Instance::ensure($this->queue, 'yii\queue\QueueInterface');
    }

    /**
     * @param $command
     * @param callable $next
     * @return string
     */
    public function execute($command, callable $next)
    {
        if ($command instanceof QueuedCommand && !$command->isRunningInQueue()) {
            return $this->queue->push(
                [
                    'serializer' => $this->serializer,
                    'object' => call_user_func($this->serializer[0], $command)
                ],
                $command->getQueueName()
            );
        }

        return $next($command);
    }
}