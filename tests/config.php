<?php
/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
return [

    'id' => 'testapp',
    'basePath' => __DIR__,
    'vendorPath' => dirname(__DIR__) . '/vendor',

    'controllerMap' => [
        'background-bus' => 'trntv\bus\console\BackgroundBusController',
        'queue-bus' => 'trntv\bus\console\QueueBusController',
    ],
    'components' => [
        'commandBus' => [
            'class' => 'trntv\bus\CommandBus',
            'locator' => 'trntv\bus\locators\ClassNameLocator',
            'middlewares' => [
                [
                    'class' => '\trntv\bus\middlewares\BackgroundCommandMiddleware',
                    'backgroundHandlerPath' => __DIR__ . '/yii.php',
                    'backgroundHandlerRoute' => 'background-bus/handle',
                    'backgroundHandlerArguments' => ['--interactive=0'],
                    'backgroundHandlerBinaryArguments' => ['-d foo=bar'],
                    'backgroundProcessTimeout' => 5
                ],
                [
                    'class' => '\trntv\bus\middlewares\QueuedCommandMiddleware'
                ],
                [
                    'class' => '\trntv\bus\middlewares\LoggingMiddleware',
                    'level' => 1,
                ],
            ],
        ],
        'queue' => [
            'class' => '\yii\queue\file\Queue',
            'path' => '@runtime/queue'
        ],
    ]
];
