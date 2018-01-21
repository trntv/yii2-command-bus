<?php

namespace trntv\bus\tests\data;

use trntv\bus\interfaces\Handler;
use yii\base\BaseObject;

/**
 * Class TestHandler
 * @package trntv\bus\tests\data
 * @author Eugene Terentev <eugene@terentev.net>
 */
class TestHandler extends BaseObject implements Handler
{
    /**
     * @param $command
     * @return mixed
     */
    public function handle($command)
    {
        return $command->param;
    }
}
