<?php
/**
 * @author Eugene Terentev <eugene@terentev.net>
 */

namespace trntv\bus\tests;

use trntv\bus\tests\data\QueuedTestCommand;

class QueuedMiddlewareTest extends TestCase
{
    public function testQueuedCommand()
    {
        $result = $this->commandBus->handle(new QueuedTestCommand());
        $this->assertInternalType('string', $result);
    }

    public function testQueuedCommandGetDelay()
    {
        $command = new QueuedTestCommand();
        $this->assertEquals(10, $command->getDelay());
        $command->delay = 5;
        $this->assertEquals(5, $command->getDelay());
    }
}
