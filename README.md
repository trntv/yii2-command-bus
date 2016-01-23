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

to the require section of your `composer.json` file.

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

For the background commands worker, set a controller in your console config

```php
'controllerMap' => [
    'bus' => [
        'class' => 'trnv\bus\console\CommandBusController',
    ]
],

'components' => [
        'commandBus' =>[
            ...
            'backgroundHandlerPath' => '@console/yii',
            'backgroundHandlerRoute' => 'bus/handle',
            ...            
        ]
        
],
```

If you need commands to be run in queue set the queue component.
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
    ]
]
```

## Usage
1. Create command that will be executed in background async mode
```php
class ReportCommand extends Command implements BackgroundCommand, SelfHandlingCommand
{
    public $async = true;
    
    public $someImportantData;
    
    public function handle() {
        // do what you needs
    }
}
```
2. Command Bus will handle the rest
```php
Yii::$app->commandBus->handle(new ReportCommand([
    'someImportantData' => [ // data // ]
]))
```
