<?php

namespace trntv\bus\base\interfaces;

/**
 * Interface QueuedCommand
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface QueuedCommand extends Command
{
    /**
     * @return bool
     */
    public function isRunningInQueue();

    /**
     * @return string
     */
    public function getQueueName();
}
