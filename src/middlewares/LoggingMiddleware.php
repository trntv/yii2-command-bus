<?php

namespace trntv\bus\middlewares;

use Yii;
use yii\base\Object;
use yii\di\Instance;
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
     * @var string|array|callable|Logger Logger configuration
     */
    public $logger;
    /**
     * @var bool
     */
    public $forceFlush = false;

    /**
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        if ($this->logger === null) {
            $this->logger = Yii::getLogger();
        } else {
            $this->logger = Yii::createObject($this->logger);
        }
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
        $this->logger->log("Command execution started: {$class}", $this->level, $this->category);
        if ($this->forceFlush) {
            $this->logger->flush($final = false);
        }
        $result = $next($command);
        $this->logger->log("Command execution ended: {$class}", $this->level, $this->category);
        if ($this->forceFlush) {
            $this->logger->flush($final = false);
        }
        return $result;
    }
}
