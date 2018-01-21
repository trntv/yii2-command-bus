<?php

namespace trntv\bus\tests\data;

use trntv\bus\interfaces\SelfHandlingCommand;
use yii\base\BaseObject;

/**
 * Class TestCommand
 * @package trntv\bus\tests\data
 * @author Eugene Terentev <eugene@terentev.net>
 */
class TestCommand extends BaseObject implements SelfHandlingCommand
{
    public function handle($command)
    {
        return 'test ok';
    }
}
