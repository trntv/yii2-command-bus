#!/usr/bin/env php
<?php
// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));
define('ROOT_PATH', __DIR__);

require(__DIR__ . '/bootstrap.php');

$config = require(__DIR__ . '/config.php');

$application = new yii\console\Application($config);

$exitCode = $application->run();
exit($exitCode);