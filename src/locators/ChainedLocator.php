<?php

namespace trntv\bus\locators;

use trntv\bus\CommandBus;
use trntv\bus\interfaces\Command;
use trntv\bus\interfaces\HandlerLocator;
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
            $this->locators[$k] = Instance::ensure($config, 'trntv\bus\interfaces\HandlerLocator');
        }
        parent::init();
    }

    /**
     * @param $command
     * @return mixed
     */
    public function locate($command)
    {
        foreach ($this->locators as $locator) {
            /** @var HandlerLocator $locator */
            $handler = $locator->locate($command);
            if ($handler) {
                return $handler;
            }
        }

        return false;
    }
}