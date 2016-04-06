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
     * @var bool
     */
    protected $runningInQueue = false;

    /**
     * @param mixed $queueName
     */
    public function setQueueName($queueName)
    {
        $this->queueName = $queueName;
    }

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
    public function setRunningInQueue($runningInQueue)
    {
        $this->runningInQueue = $runningInQueue;
    }

    /**
     * @return mixed
     */
    public function getQueueName()
    {
        $queueName = null;
        if (property_exists(get_called_class(), 'queueName') && $this->queueName) {
            $queueName = $this->queueName;
        }
        return $queueName;
    }

    /**
     * @return null|int
     */
    public function getDelay()
    {
        $delay = null;
        if (property_exists(get_called_class(), 'delay')) {
            $delay = $this->delay;
        }
        return $delay;
    }
}