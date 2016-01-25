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
     * @var string
     */
    protected $queueName;
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
        if (!$this->queueName) {
            $queueName = self::className();
        } else {
            $queueName = $this->queueName;
        }
        return $queueName;
    }
}