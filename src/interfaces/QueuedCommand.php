<?php

namespace trntv\bus\interfaces;

/**
 * Interface QueuedCommand
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
interface QueuedCommand
{
    /**
     * @return bool
     */
    public function isRunningInQueue();

    /**
     * @return int
     */
    public function getDelay();
}
