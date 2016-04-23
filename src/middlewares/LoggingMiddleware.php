<?php

namespace trntv\bus\middlewares;

use Yii;
use yii\base\Object;
use yii\log\Logger;
use trntv\bus\interfaces\Middleware;

/**
 * Class LoggingMiddleware
 * @package trntv\bus\middlewares
 * @author Eugene Terentev <eugene@terentev.net>
 */
class LoggingMiddleware extends Object implements Middleware
{
    /**
     * @var integer log message level
     */
    public $level;

    /**
     * @var string log message category
     */
    public $category = 'command-bus';

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
        $class = get_class($command);
        Yii::getLogger()->log("Command execution started: {$class}", $this->level, $this->category);
        $result = $next($command);
        Yii::getLogger()->log("Command execution ended: {$class}", $this->level, $this->category);
        return $result;
    }
}