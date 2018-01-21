<?php

namespace trntv\bus\locators;

use trntv\bus\interfaces\Handler;
use trntv\bus\interfaces\HandlerLocator;
use yii\base\BaseObject;
use yii\di\Instance;

/**
 * Class ClassNameLocator
 *
 * @author Eugene Terentev <eugene@terentev.net>
 */
class ClassNameLocator extends BaseObject implements HandlerLocator
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
            return Instance::ensure($this->handlers[$className], Handler::class);
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
