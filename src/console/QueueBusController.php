<?php

namespace trntv\bus\console;

use trntv\bus\interfaces\QueuedCommand;
use trntv\bus\CommandBus;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\Json;
use yii\queue\QueueInterface;


/**
 * Class QueueBusController
 * @package trntv\bus\console
 * @author Eugene Terentev <eugene@terentev.net>
 */
class QueueBusController extends Controller
{
    const EVENT_BEFORE_HANDLE = 'beforeHandle';
    const EVENT_AFTER_HANDLE = 'afterHandle';
    /**
     * @var mixed|QueueInterface
     */
    public $queue = 'queue';
    /**
     * @var mixed|CommandBus
     */
    public $commandBus = 'commandBus';
    /**
     * @var int
     */
    public $sleep = 3;
    /**
     * @var int
     */
    public $memoryLimit;
    /**
     * @var bool If "true" command will be removed from queue after has been picked up
     */
    public $forceDelete = false;
    /**
     * @var bool
     */
    public $end = false;
    /**
     * @var bool
     */
    public $dispatchSignal = false;

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function beforeAction($action)
    {
        $this->queue = Instance::ensure($this->queue, '\yii\queue\QueueInterface');
        $this->commandBus = Instance::ensure($this->commandBus, '\trntv\bus\CommandBus');
        return parent::beforeAction($action);
    }

    /**
     * @param string $queueName
     */
    public function actionListen($queueName)
    {
        Console::output("Listening queue \"{$queueName}\"");

        // Run loop
        try {
            $this->loop($queueName);
        } catch (\Exception $e) {
            Console::error($e->getMessage());
        }
    }

    /**
     * @param $queueName
     */
    protected function loop($queueName)
    {
        while(!$this->end) {
            $job = $this->queue->pop($queueName);
            $wasDeleted = false;

            // Handle job
            if ($job) {

                $id = ArrayHelper::getValue($job, 'id');
                Console::output("New job ID#{$id}");

                if(!$this->beforeHandle($job)) {
                    continue;
                };

                try {
                    if ($this->forceDelete) {
                        $this->delete($job);
                        $wasDeleted = true;
                    }

                    $result = $this->handle($job);
                    if (!$wasDeleted) {
                        $this->delete($job);
                    }
                    $this->onSuccess($job, $result);
                } catch (\Exception $e) {
                    $this->onError($job, $e);
                }

                $this->afterHandle($job);
                unset($id, $job);
            } else {
                sleep($this->sleep);
            }

            // Check memory usage
            if ($this->memoryLimit) {
                if ($this->memoryExceeded($this->memoryLimit)) {
                    Yii::warning('Queue restarted due to memory limit', 'queue:listen');
                    $this->end();
                }
            }

            if ($this->dispatchSignal) {
                if (!extension_loaded('pcntl')) {
                    throw new InvalidConfigException('pcntl extension should be installed');
                }
                pcntl_signal_dispatch();
            }
        }
        $this->end();
    }

    /**
     * @param $job
     * @return mixed
     */
    protected function beforeHandle($job)
    {
        $event = new QueueBusEvent([
            'job' => $job
        ]);
        $this->trigger(self::EVENT_BEFORE_HANDLE, $event);
        return $event->isValid;
    }

    /**
     * @param $job
     * @return mixed|string
     */
    protected function handle($job)
    {
        if (array_key_exists('serializer', $job['body'])) {
            $unserialize = $job['body']['serializer'][1];
            $command = call_user_func($unserialize, $job['body']['object']);
            if ($command instanceof QueuedCommand) {
                $command->setRunningInQueue(true);
                return $this->commandBus->handle($command);
            }
        }
        Console::error("Malformed job ID#{$job['id']}");
        $this->delete($job);
    }

    /**
     * @param $job
     */
    protected function afterHandle($job)
    {
        $this->trigger(self::EVENT_AFTER_HANDLE, new QueueBusEvent([
            'job' => $job
        ]));
    }

    /**
     * @param $job
     */
    protected function delete($job)
    {
        $this->queue->delete([
            'queue' => $job['queue'],
            'body' => Json::encode([
                'id' => $job['id'],
                'body' => $job['body']
            ])
        ]);
        Console::output("Job ID#{$job['id']} was deleted from queue");
    }

    /**
     * @param $job
     * @param $result
     */
    protected function onSuccess($job, $result = null)
    {
        Console::output("Job ID#{$job['id']} has been successfully done");
    }

    /**
     * @param $exception
     * @param $job
     */
    protected function onError($job, \Exception $exception = null)
    {
        Console::error("Job ID#{$job['id']}: {$exception->getMessage()}");
    }

    /**
     * Determine if the memory limit has been exceeded.
     *
     * @param  int  $memoryLimit
     * @return bool
     */
    public function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * @throws \yii\base\ExitException
     */
    public function end()
    {
        Console::output('Exiting queue listener');
        Yii::$app->end();
    }
}
