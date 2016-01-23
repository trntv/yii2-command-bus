<?php

namespace trntv\bus\tests\data;

use trntv\bus\interfaces\SelfHandlingCommand;
use trntv\bus\Command;

/**
 * Class TestCommand
 * @package trntv\bus\tests\data
 * @author Eugene Terentev <eugene@terentev.net>
 */
class TestCommand extends Command implements SelfHandlingCommand
{
    public function handle()
    {
        return 'test ok';
    }
}