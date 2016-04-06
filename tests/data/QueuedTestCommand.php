<?php

namespace trntv\bus\tests\data;

use trntv\bus\interfaces\QueuedCommand;
use trntv\bus\interfaces\SelfHandlingCommand;
use trntv\bus\middlewares\QueuedCommandTrait;
use yii\base\Object;

/**
 * Class QueuedTestCommand
 * @package trntv\bus\tests\data
 * @author Eugene Terentev <eugene@terentev.net>
 */
class QueuedTestCommand extends Object implements SelfHandlingCommand, QueuedCommand
{
    use QueuedCommandTrait;
    
    public $delay = 10;

    public function handle($command)
    {
        return true;
    }
}