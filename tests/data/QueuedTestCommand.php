<?php

namespace trntv\bus\tests\data;

use trntv\bus\interfaces\QueuedCommand;
use trntv\bus\interfaces\SelfHandlingCommand;
use trntv\bus\middlewares\QueuedCommandTrait;
use yii\base\BaseObject;

/**
 * Class QueuedTestCommand
 * @package trntv\bus\tests\data
 * @author Eugene Terentev <eugene@terentev.net>
 */
class QueuedTestCommand extends BaseObject implements SelfHandlingCommand, QueuedCommand
{
    use QueuedCommandTrait;

    public function handle($command)
    {
        \file_put_contents(\Yii::getAlias('@runtime/test.lock'), __CLASS__);
        return true;
    }
}
