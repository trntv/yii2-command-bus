<?php
/**
 * @author Eugene Terentev <eugene@terentev.net>
 */

namespace trntv\bus\tests;

use Symfony\Component\Process\Process;
use trntv\bus\tests\data\BackgroundTestCommand;
use trntv\bus\tests\data\TestCommand;
use trntv\bus\tests\data\TestHandler;
use trntv\bus\tests\data\TestHandlerCommand;
use trntv\bus\tests\data\TestMiddleware;
use yii\helpers\Console;

class CommandBusTest extends TestCase
{
    public function testCommand()
    {
        $result = $this->commandBus->handle(new TestCommand());
        $this->assertEquals('test ok', $result);
    }

    public function testBackgroundCommand()
    {
        /** @var $process Process */
        $process = $this->commandBus->handle(new BackgroundTestCommand());

        Console::output($process->getOutput());
        Console::output($process->getErrorOutput());

        $this->assertTrue($process->isSuccessful());
        $this->assertEquals('test ok', file_get_contents(__DIR__ . '/files/test-file'));
    }

    public function testMiddleware()
    {
        $this->commandBus->addMiddleware(new TestMiddleware());
        $result = $this->commandBus->handle(new TestCommand());
        $this->assertEquals('test ok', $result);
        $this->assertNotFalse(array_search('middleware test 1 ok', \Yii::$app->getLog()->logger->messages[0]));
        $this->assertNotFalse(array_search('middleware test 2 ok', \Yii::$app->getLog()->logger->messages[1]));
    }

    public function testHandler()
    {
        $this->commandBus->locator->addHandler(new TestHandler(), TestHandlerCommand::className());
        $result = $this->commandBus->handle(new TestHandlerCommand([
            'param' => 'test ok'
        ]));
        $this->assertEquals('test ok', $result);
    }

    public function tearDown()
    {
        parent::tearDown();
        @unlink(__DIR__ . '/files/test-file');
    }

}
