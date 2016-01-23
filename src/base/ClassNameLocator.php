<?php

namespace trntv\bus\base;
use trntv\bus\base\interfaces\Command;
use trntv\bus\base\interfaces\Handler;
use trntv\bus\base\interfaces\HandlerLocator;
use yii\base\Object;
use yii\di\Instance;


/**
 * Class ClassNameLocator
 * @package trntv\bus\base
 * @author Eugene Terentev <eugene@terentev.net>
 */
class ClassNameLocator extends Object implements HandlerLocator
{
    /**
     * @var array|Handler[]
     */
    public $handlers = [];

    /**
     * @param Command $command
     * @return mixed
     */
    public function locate(Command $command)
    {
        $type = get_class($command);

        if (array_key_exists($type, $this->handlers)) {
            return Instance::ensure($this->handlers[$type], 'trntv\buse\base\interfaces\HandlerLocator');
        }

        return false;
    }


}