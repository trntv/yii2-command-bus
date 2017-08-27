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
#### 3.1 Install required package:

```
php composer.phar require yiisoft/yii2-queue
```
#### 3.2 Configure extensions
If you need commands to be run in queue, you need to setup middleware and yii2-queue extensions.

```php
'components' => [
    'queue' => [
        // queue config
    ],
    
    'commandBus' =>[
        ...
        'middlewares' => [
            [
                'class' => '\trntv\bus\middlewares\QueuedCommandMiddleware',
                // 'delay' => 3, // You can set default delay for all commands here
            ]                
        ]
        ...            
    ]     
]
```
More information about yii2-queue config can be found [here](https://github.com/yiisoft/yii2-queue/blob/master/docs/guide/usage.md)
#### 3.4 Run queue worker 
``yii queue/listen``
More information [here](https://github.com/yiisoft/yii2-queue/blob/master/docs/guide/worker.md#worker-starting-control) 
#### 3.5 Create and run command
```php
class HeavyComputationsCommand extends Object implements QueuedCommand, SelfHandlingCommand
{
    use QueuedCommandTrait;
    
    public function handle() {
        // do work here
    }
}

$command = new HeavyComputationsCommand();
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
