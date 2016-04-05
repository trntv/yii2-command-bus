<?php

namespace trntv\bus\middlewares;

use trntv\bus\interfaces\Middleware;
use Yii;
use yii\base\Object;
use yii\log\Logger;

/**
 * Class LoggingMiddleware
 * @package trntv\bus\middlewares
 * @author Eugene Terentev <eugene@terentev.net>
 */
class LoggingMiddleware extends Object implements Middleware
{
    /**
     * @var integer the level of log message
     */
    public $level;

    /**
     * @return void
     */
    public function init()
    {
        if (!$this->level) {
            $this->level = Logger::LEVEL_INFO;
        }
    }

    /**
     * @param $command
     * @param callable $next
     * @return mixed
     */
    public function execute($command, callable $next)
    {
        $command = get_class($command);
        Yii::getLogger()->log("Command execution started: {$command}", $this->level, 'command-bus');
        $result = $next($command);
        Yii::getLogger()->log("Command execution ended: {$command}", $this->level, 'command-bus');
        return $result;
    }
}