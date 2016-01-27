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
                    'backgroundHandlerRoute' => 'background-bus/handle'
                ],
                [
                    'class' => '\trntv\bus\middlewares\QueuedCommandMiddleware',
                    'defaultQueueName' => 'test-commands-queue'
                ]
            ],
        ],
        'queue' => [
            'class' => 'yii\queue\RedisQueue',
            'redis' => [
                'scheme' => 'tcp',
                'host'   => '127.0.0.1',
                'port'   => '6379',
            ]
        ],
    ]
];