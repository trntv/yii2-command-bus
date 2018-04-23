<?php
/**
 * @author Eugene Terentev <eugene@terentev.net>
 */

namespace trntv\bus\tests;

use trntv\bus\tests\data\QueuedTestCommand;
use yii\helpers\FileHelper;

class QueuedMiddlewareTest extends TestCase
{
    public function testQueuedCommand()
    {
        $id = $this->commandBus->handle(new QueuedTestCommand());
        $this->assertInternalType('integer', $id);

        /** @var \yii\queue\file\Queue $queue */
        $queue = \Yii::$app->get('queue');
        $queue->run(false);

        $this->assertStringEqualsFile(\Yii::getAlias('@runtime/test.lock'), QueuedTestCommand::class);
    }

    public function testQueuedCommandGetDelay()
    {
        $command = new QueuedTestCommand();
        $command->setDelay(10);
        $this->assertEquals(10, $command->getDelay());
        $command->setDelay(5);
        $this->assertEquals(5, $command->getDelay());
    }

    public function tearDown()
    {
        @\unlink(\Yii::getAlias('@runtime/test.lock'));
        FileHelper::removeDirectory(\Yii::getAlias('@runtime/queue'));
        FileHelper::createDirectory(\Yii::getAlias('@runtime/queue'));
    }
}
