<?php

namespace trntv\bus\tests\data;

use trntv\bus\base\Command;
use trntv\bus\base\interfaces\BackgroundCommand;
use trntv\bus\base\interfaces\SelfHandlingCommand;

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