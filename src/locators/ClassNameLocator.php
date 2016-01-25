<?php

namespace trntv\bus\locators;

use trntv\bus\CommandBus;
use trntv\bus\interfaces\Command;
use trntv\bus\interfaces\Handler;
use trntv\bus\interfaces\HandlerLocator;
use yii\base\Object;
use yii\di\Instance;


/**
 * Class ClassNameLocator
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
class ClassNameLocator extends Object implements HandlerLocator
{
    /**
     * @var
     */
    public $handlers;

    /**
     * @param $command
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @internal param CommandBus $commandBus
     */
    public function locate($command)
    {
        $className = get_class($command);

        if (array_key_exists($className, $this->handlers)) {
            return Instance::ensure($this->handlers[$className], 'trntv\bus\interfaces\Handler');
        }

        return false;
    }

    /**
     * @param Handler $handler
     * @param $className
     */
    public function addHandler(Handler $handler, $className)
    {
        $this->handlers[$className] = $handler;
    }
}
