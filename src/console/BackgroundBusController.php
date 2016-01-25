<?php

namespace trntv\bus\console;

use trntv\bus\CommandBus;
use yii\console\Controller;
use yii\di\Instance;
use yii\helpers\Console;

/**
 * Class BackgroundBusController
 * @package trntv\bus\console
 * @author Eugene Terentev <eugene@terentev.net>
 */
class BackgroundBusController extends Controller
{
    /**
     * @var mixed|CommandBus
     */
    public $commandBus = 'commandBus';

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function beforeAction($action)
    {
        $this->commandBus = Instance::ensure($this->commandBus, '\trntv\bus\CommandBus');
        return parent::beforeAction($action);
    }

    /**
     * @param string $command serialized command object
     * @return string
     */
    public function actionHandle($command)
    {
        try {
            $command = unserialize(base64_decode($command));
            $command->setRunningInBackground(true);
            $this->commandBus->handle($command);
        } catch (\Exception $e) {
            Console::error($e->getMessage());
        }
    }
}