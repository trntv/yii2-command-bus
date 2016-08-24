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

### 1. Command Bus Service
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

### 2. Background commands support (optional)
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

Create background command
```php
class ReportCommand extends Object implements BackgroundCommand, SelfHandlingCommand
{
    use BackgroundCommandTrait;
    
    public $someImportantData;
    
    public function handle($command) {
        // do what you need
    }
}
```
And run it asynchronously!
```php
Yii::$app->commandBus->handle(new ReportCommand([
    'async' => true,
    'someImportantData' => [ // data // ]
]))
```

### 3. Queued commands support (optional)
Install required package:

```
php composer.phar require yiisoft/yii2-queue:dev-master@dev
```

If you need commands to be run in queue, you need to set middleware, 
queue component and queue listener in your config.

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
                // 'defaultQueueName' => 'commands-queue', // You can set default queue name
                // 'delay' => 3, // You can set default delay for all commands here
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
Create and handle command
```php
class HeavyComputationsCommand extends Object implements QueuedCommand
{
    use QueuedCommandTrait;
    public $queueName = 'you-can-change-queue-name-here';
    public $delay = 5; // Command will be delayed for 5 seconds
}

$command = new ReportCommand([
    'delay' => 7, // You can change delay here
])
Yii::$app->commandBus->handle($command)
```

### 4. Handlers
Handlers are objects that will handle command execution
There are two possible ways to execute command:
#### 4.1 External handler 
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
#### 4.1 Self-handling command
```php
class SomeCommand implements SelfHandlingCommand
{
    public function handle($command) {
        // do what you need
    }
}

$command = Yii::$app->commandBus->handle($command);
```
