<?php

namespace trntv\bus\base;
use trntv\bus\base\interfaces\Command;
use trntv\bus\base\interfaces\HandlerLocator;
use yii\base\Object;
use yii\di\Instance;


/**
 * Class ChainedLocator
 * @package trntv\bus\base
 * @author Eugene Terentev <eugene@terentev.net>
 */
class ChainedLocator extends Object implements HandlerLocator
{
    /**
     * @var array|HandlerLocator[]
     */
    public $locators = [];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        foreach ($this->locators as $k => $config) {
            $this->locators[$k] = Instance::ensure($config, 'trntv\bus\base\interfaces\HandlerLocator');
        }
        parent::init();
    }

    /**
     * @param Command $command
     * @return mixed
     */
    public function locate(Command $command)
    {
        foreach ($this->locators as $locator) {
            $handler = $locator->locate($command);
            if ($handler) {
                return $handler;
            }
        }

        return false;
    }
}