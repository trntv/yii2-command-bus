<?php

namespace trntv\bus\tests;

use trntv\bus\CommandBus;
use trntv\bus\console\CommandBusController;
use yii\helpers\ArrayHelper;
use yiiunit\TestCase as BaseTestCase;

/**
 * Class TestCase
 * @package trntv\bus\tests
 * @author Eugene Terentev <eugene@terentev.net>
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @var CommandBus
     */
    public $commandBus;

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        $config = ArrayHelper::merge(require(__DIR__ . '/config.php'), $config);
        parent::mockApplication($config, $appClass);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->commandBus = \Yii::$app->get('commandBus');
    }
}
