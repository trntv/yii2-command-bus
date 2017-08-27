<?php

namespace trntv\bus\middlewares;

use trntv\bus\CommandBus;
use yii\base\Object;
use yii\di\Instance;
use yii\queue\Job;
use yii\queue\Queue;

/**
 *
 */
class QueuedJobWrapper extends Object implements Job
{
    /**
     * @var mixed
     */
    public $command;

    /**
     * @var string|array
     */
    public $commandBus;

    /**
     * @param Queue $queue
     */
    public function execute($queue)
    {
        $commandBus = Instance::ensure($this->commandBus ?: CommandBus::class, CommandBus::class);
        $commandBus->handle($this->command);
    }
}
