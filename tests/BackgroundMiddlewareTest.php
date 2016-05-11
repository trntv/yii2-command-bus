<?php
/**
 * @author Eugene Terentev <eugene@terentev.net>
 */

namespace trntv\bus\tests;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
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
        $this->assertEquals('test ok', $process->getOutput());
    }

    public function testBackgroundProcessArguments()
    {
        /** @var $process Process */
        $process = $this->commandBus->handle(new BackgroundTestCommand());
        $this->assertContains('-d foo=bar', $process->getCommandLine());
        $this->assertContains('--interactive=0', $process->getCommandLine());
    }

    public function testBackgroundAsyncCommand()
    {
        $command = new BackgroundTestCommand([
            'async' => true
        ]);
        for ($i = 0; $i < 10; $i++) {
            /** @var $process Process */
            $process = $this->commandBus->handle($command);
            $this->assertInstanceOf(Process::class, $process);
            while ($process->isRunning()) {
                // waiting for process to finish
            }
            $this->assertTrue($process->isSuccessful());
            $output = $process->getOutput();
            $this->assertEquals('test ok', $output);
            $this->assertNotEquals('test is not ok', $output);
        }
    }

    public function testBackgroundCommandTimeout() {
        $commandWithTimeout = new BackgroundTestCommand([
            'async' => true,
            'sleep' => 6
        ]);
        /** @var $process Process */
        $process = $this->commandBus->handle($commandWithTimeout);
        $this->assertInstanceOf(Process::class, $process);
        try {
            while ($process->isRunning()) {
                $process->checkTimeout();
            }
            $this->fail('Timeout not working');
        } catch (ProcessTimedOutException $e) {}
    }

    public function tearDown()
    {
        parent::tearDown();
        @unlink(__DIR__ . '/files/test-file');
    }
}
