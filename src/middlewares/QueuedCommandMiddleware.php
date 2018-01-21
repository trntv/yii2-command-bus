<?php

namespace trntv\bus\middlewares;

use trntv\bus\interfaces\Middleware;
use trntv\bus\interfaces\QueuedCommand;
use yii\base\BaseObject;
use yii\di\Instance;
use yii\queue\Queue;

/**
 * Class QueuedCommandMiddleware
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
class QueuedCommandMiddleware extends BaseObject implements Middleware
{
    /**
     * @var mixed|Queue
     */
    public $queue = 'queue';

    /**
     * @var int Default delay for all commands
     */
    public $delay = 0;

    /**
     * @param string|array Command bus component name or configuration array
     * compatible with Yii's createObject() method
     */
    public $commandBus;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->queue = Instance::ensure($this->queue, Queue::class);
    }

    /**
     * @param $command
     * @param callable $next
     *
     * @return string
     */
    public function execute($command, callable $next)
    {
        if ($command instanceof QueuedCommand && !$command->isRunningInQueue())
        {
            $delay = $command->getDelay() !== null ?: $this->delay;

            $command->setRunningInQueue(true);
            $job = new QueuedJobWrapper([
                'command' => $command,
                'commandBus' => $this->commandBus
            ]);

            return $this->queue->delay($delay)->push($job);
        }

        return $next($command);
    }
}
