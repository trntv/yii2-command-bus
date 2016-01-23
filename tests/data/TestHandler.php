<?php

namespace trntv\bus\tests\data;
use trntv\bus\interfaces\Command;
use trntv\bus\interfaces\Handler;
use yii\base\Object;


/**
 * Class TestHandler
 * @package trntv\bus\tests\data
 * @author Eugene Terentev <eugene@terentev.net>
 */
class TestHandler extends Object implements Handler
{
    /**
     * @param Command $command
     * @return mixed
     */
    public function handle(Command $command)
    {
        return $command->param;
    }
}