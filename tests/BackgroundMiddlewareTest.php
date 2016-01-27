<?php
/**
 * @author Eugene Terentev <eugene@terentev.net>
 */

namespace trntv\bus\tests;

use Symfony\Component\Process\Process;
use trntv\bus\tests\data\BackgroundTestCommand;

class BackgroundMiddlewareTest extends TestCase
{
    public function testBackgroundCommand()
    {
        /** @var $process Process */
        $process = $this->commandBus->handle(new BackgroundTestCommand());
        $this->assertInstanceOf(Process::class, $process);
        $this->assertTrue($process->isSuccessful());
        $this->assertEquals('test ok', file_get_contents(__DIR__ . '/files/test-file'));
    }

    public function tearDown()
    {
        parent::tearDown();
        @unlink(__DIR__ . '/files/test-file');
    }
}
