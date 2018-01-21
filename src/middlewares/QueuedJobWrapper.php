<?php

namespace trntv\bus\middlewares;

use trntv\bus\CommandBus;
use yii\base\BaseObject;
use yii\di\Instance;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 *
 */
class QueuedJobWrapper extends BaseObject implements JobInterface
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
     * @throws \yii\base\InvalidConfigException
     */
    public function execute($queue)
    {
        $commandBus = Instance::ensure($this->commandBus ?: CommandBus::class, CommandBus::class);
        $commandBus->handle($this->command);
    }
}
