<?php

namespace trntv\bus\tests;

use trntv\bus\CommandBus;
use yii\helpers\ArrayHelper;

/**
 * Class TestCase
 * @package trntv\bus\tests
 * @author Eugene Terentev <eugene@terentev.net>
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
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
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => $this->getVendorPath(),
        ], $config));
    }

    /**
     * @return string
     */
    protected function getVendorPath()
    {
        $vendor = dirname(dirname(__DIR__)) . '/vendor';
        if (!is_dir($vendor)) {
            $vendor = dirname(dirname(dirname(dirname(__DIR__))));
        }
        return $vendor;
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
