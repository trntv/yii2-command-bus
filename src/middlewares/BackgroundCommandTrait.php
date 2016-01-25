<?php

namespace trntv\bus\middlewares;


/**
 * Class BackgroundCommandTrait
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
trait BackgroundCommandTrait
{
    /**
     * @var bool
     */
    protected $async = false;
    /**
     * @var bool
     */
    protected $runningInBackground = false;

    /**
     * @return bool
     */
    public function isAsync()
    {
        return $this->async;
    }
    /**
     * @param boolean $runningInBackground
     */
    public function setRunningInBackground($runningInBackground = true)
    {
        $this->runningInBackground = $runningInBackground;
    }

    /**
     * @param boolean $async
     */
    public function setAsync($async = true)
    {
        $this->async = $async;
    }

    /**
     * @return bool
     */
    public function isRunningInBackground()
    {
        return $this->runningInBackground;
    }
}