<?php
error_reporting(E_ALL);

defined('YII_ENABLE_ERROR_HANDLER') OR define('YII_ENABLE_ERROR_HANDLER', false);
defined('YII_DEBUG') OR define('YII_DEBUG', true);

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@trntv/bus/tests', __DIR__);
Yii::setAlias('@trntv/bus', dirname(__DIR__));
Yii::setAlias('@yiiunit', dirname(__DIR__) . '/vendor/yiisoft/yii2-dev/tests');