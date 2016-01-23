<?php

namespace trntv\bus;

use yii\base\Object;
use trntv\bus\interfaces\Command as CommandInterface;

/**
 * Class Command
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
abstract class Command extends Object implements CommandInterface
{
    /**
     * @var bool
     */
    protected $async = false;

    /**
     * @var string
     */
    protected $queueName;

    /**
     * @var bool
     */
    protected $runningInQueue = false;

    /**
     * @var bool
     */
    protected $runningInBackground = false;

    /**
     * @param boolean $async
     */
    public function setAsync($async)
    {
        $this->async = $async;
    }

    /**
     * @return bool
     */
    public function isAsync()
    {
        return $this->async;
    }

    /**
     * @param mixed $queueName
     */
    public function setQueueName($queueName)
    {
        $this->queueName = $queueName;
    }

    /**
     * @param boolean $runningInBackground
     */
    public function setRunningInBackground($runningInBackground)
    {
        $this->runningInBackground = $runningInBackground;
    }


    /**
     * @return bool
     */
    public function isRunningInBackground()
    {
        return $this->runningInBackground;
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