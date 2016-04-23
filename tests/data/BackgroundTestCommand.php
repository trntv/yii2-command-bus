<?php

namespace trntv\bus\tests\data;

use trntv\bus\Command;
use trntv\bus\interfaces\BackgroundCommand;
use trntv\bus\interfaces\SelfHandlingCommand;
use trntv\bus\middlewares\BackgroundCommandTrait;
use yii\base\Object;

/**
 * Class BackgroundTestCommand
 * @package trntv\bus\tests\data
 * @author Eugene Terentev <eugene@terentev.net>
 */
class BackgroundTestCommand extends Object implements BackgroundCommand, SelfHandlingCommand
{
    use BackgroundCommandTrait;

    public function handle($command)
    {
        echo 'test ok';
    }
}