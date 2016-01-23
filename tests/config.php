<?php
/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
return [

    'id' => 'testapp',
    'basePath' => __DIR__,
    'vendorPath' => dirname(__DIR__) . '/vendor',

    'controllerMap' => [
        'bus' => 'trntv\bus\console\CommandBusController'
    ],
    'components' => [
        'commandBus' => [
            'class' => 'trntv\bus\CommandBus',
            'handlers' => [
                ''
            ],
            'backgroundHandlerPath' => __DIR__ . '/yii.php',
            'backgroundHandlerRoute' => 'bus/handle'
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