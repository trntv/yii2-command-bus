<?php

namespace trntv\bus\tests\data;

use trntv\bus\Command;
use trntv\bus\interfaces\BackgroundCommand;
use trntv\bus\interfaces\SelfHandlingCommand;

/**
 * Class BackgroundTestCommand
 * @package trntv\bus\tests\data
 * @author Eugene Terentev <eugene@terentev.net>
 */
class BackgroundTestCommand extends Command implements BackgroundCommand, SelfHandlingCommand
{

    public function handle()
    {
        file_put_contents(dirname(__DIR__) . '/files/test-file', 'test ok');
    }
}