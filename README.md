# Yii2 Command Bus

Command Bus for Yii2

[![Build Status](https://travis-ci.org/trntv/yii2-command-bus.svg?branch=master)](https://travis-ci.org/trntv/yii2-command-bus)


## What is Command Bus? 
> The idea of a command bus is that you create command objects that represent what you want your application to do. 
> Then, you toss it into the bus and the bus makes sure that the command object gets to where it needs to go.
> So, the command goes in -> the bus hands it off to a handler -> and then the handler actually does the job. The command essentially represents a method call to your service layer.

[Shawn McCool ©](http://shawnmc.cool/command-bus)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist trntv/yii2-command-bus
```

or add
```
"trntv/yii2-command-bus": "^1.0"
```
to your composer.json file

## Setting Up

After the installation, first step is to set the command bus component.

```php
return [
    // ...
    'components' => [
        'commandBus' => [
            'class' => 'trntv\bus\CommandBus'
        ]
    ],
];
```

### Background commands
Install required package:
```
php composer.phar require symfony/process:^3.0
```

For the background commands worker, add a controller and command bus middleware in your config

```php
'controllerMap' => [
    'background-bus' => [
        'class' => 'trntv\bus\console\BackgroundBusController',
    ]
],

'components' => [
        'commandBus' =>[
            ...
            'middlewares' => [
                [
                    'class' => '\trntv\bus\middlewares\BackgroundCommandMiddleware',
                    'backgroundHandlerPath' => '@console/yii',
                    'backgroundHandlerRoute' => 'background-bus/handle',
                ]                
            ]
            ...            
        ]        
],
```

### Queued commands
Install required package:
```
php composer.phar require yiisoft/yii2-queue:dev-master@dev
```

If you need commands to be run in queue set middleware, queue component and queue listener in your config.
For example, queue using Redis

```php
'components' => [
    'queue' => [
        'class' => 'yii\queue\RedisQueue',
        'redis' => [
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => '6379',
        ]
    ],
    
    'commandBus' =>[
        ...
        'middlewares' => [
            [
                'class' => '\trntv\bus\middlewares\QueuedCommandMiddleware',
                // 'defaultQueueName' => 'commands-queue' // You can set default queue name
            ]                
        ]
        ...            
    ]     
],
'controllerMap' => [
    'queue-bus' => [
        'class' => '\trntv\bus\console\QueueBusController'
    ]
]
```
then run console command to listen queue:

```
php yii queue-bus/listen some-queue-name
```

## Usage
1. Create command that will be executed in background async mode

```php
class ReportCommand extends Object implements BackgroundCommand, SelfHandlingCommand
{
    use BackgroundCommandTrait;
    
    public $async = true;
    
    public $someImportantData;
    
    public function handle($command) {
        // do what you need
    }
}
```

2. Command Bus will handle the rest
```php
Yii::$app->commandBus->handle(new ReportCommand([
    'someImportantData' => [ // data // ]
]))
```

## Handlers
Handlers are objects that will handle command execution
```php
return [
    // ...
    'components' => [
        'commandBus' => [
            'class' => 'trntv\bus\CommandBus',
            'locator' => [
                'class' => 'trntv\bus\locators\ClassNameLocator',
                'handlers' => [
                    'app\commands\SomeCommand' => 'app\handlers\SomeHandler'
                ]
            ]
        ]
    ],
];

// or
$handler = new SomeHandler;
Yii::$app->commandBus->locator->addHandler($handler, 'app\commands\SomeCommand');
// or
Yii::$app->commandBus->locator->addHandler('app\handlers\SomeHandler', 'app\commands\SomeCommand');
```
