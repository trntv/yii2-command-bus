<?php

namespace trntv\bus\middlewares;


/**
 * Class QueuedCommandTrait
 * @package trntv\bus\middlewares
 * @author Eugene Terentev <eugene@terentev.net>
 */
trait QueuedCommandTrait
{
    /**
     * @var int
     */
    protected $delay;

    /**
     * @var bool
     */
    protected $runningInQueue = false;

    /**
     * @return boolean
     */
    public function isRunningInQueue()
    {
        return $this->runningInQueue;
    }

    /**
     * @param boolean $runningInQueue
     */
    public function setRunningInQueue($runningInQueue = true)
    {
        $this->runningInQueue = $runningInQueue;
    }

    /**
     * @return null|int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * @param int|null $delay
     *
     * @return void
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;
    }
}
