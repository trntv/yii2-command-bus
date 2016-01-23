<?php

namespace trntv\bus\console;

use trntv\bus\interfaces\QueuedCommand;
use trntv\bus\CommandBus;
use Yii;
use yii\console\Controller;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\Json;
use yii\queue\QueueInterface;


/**
 * Class CommandBusController
 * @package trntv\bus\console
 * @author Eugene Terentev <eugene@terentev.net>
 */
class CommandBusController extends Controller
{
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
     * @var int
     */
    public $maxAttempts = 1;
    /**
     * @var int
     */
    public $nextAttemptDelay = 10;
    /**
     * @var bool
     */
    public $forceDelete = false;

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
     * @param string $command serialized command object
     * @return string
     */
    public function actionHandle($command)
    {
        try {
            $command = $this->commandBus->unserializeBackgroundCommand($command);
            $command->setRunningInBackground(true);
            $this->commandBus->handle($command);
        } catch (\Exception $e) {
            Console::error($e->getMessage());
        }
    }

    /**
     * @param string $queueName
     */
    public function actionQueue($queueName)
    {
        Console::output("Listening queue \"{$queueName}\"");

        // Run loop
        while(true) {
            $job = $this->queue->pop($queueName);

            if ($this->forceDelete) {
                $this->delete($job);
            }

            // Handle job
            if ($job) {

                $jobID = ArrayHelper::getValue($job, 'id');
                $attempt = $this->getJobMeta($job, 'attempt', 0);
                Console::output("Handling job ID#{$jobID}; Attempt: {$attempt}");

                try {
                    $this->handle($job);
                    Console::output("Job #{$jobID} has been successfully done");
                } catch (\Exception $e) {
                    Yii::error($e->getMessage(), 'queue:listen');
                    Console::error($e->getMessage());
                    if (++$attempt < $this->maxAttempts) {
                        $job = $this->setJobMeta($job, 'attempt', $attempt);
                        $this->release($job, $this->nextAttemptDelay);
                    }
                }

                $this->delete($job);
            }

            // Check memory usage
            if ($this->memoryLimit) {
                if ($this->memoryExceeded($this->memoryLimit)) {
                    Yii::warning('Queue restarted due to memory limit', 'queue:listen');
                    $this->end();
                }
            }

            sleep($this->sleep);
        }
    }

    /**
     * @param $job
     * @return mixed|string
     */
    protected function handle($job)
    {
        if (array_key_exists('serializer', $job['body'])) {
            $command = call_user_func($job['body']['serializer'][1], $job['body']['object']);
            if ($command instanceof QueuedCommand) {
                $command->setRunningInQueue(true);
                return $this->commandBus->handle($command);
            }
        }
        Console::error('Malformed job ID#' . $job['id']);
        $this->delete($job);
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
    }

    /**
     * @param $job
     * @param int $delay
     */
    protected function release($job, $delay = 0)
    {
        $this->queue->release([
            'queue' => $job['queue'],
            'body' => Json::encode([
                'id' => $job['id'],
                'body' => $job['body']
            ])
        ], $delay);
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

    /**
     * @param $job
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function setJobMeta($job, $key, $value)
    {
        if (!array_key_exists('_meta', $job) || !is_array($job['_meta'])) {
            $job['_meta'] = [];
        }

        $job['_meta'][$key] = $value;

        return $job;
    }

    /**
     * @param $job
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    protected function getJobMeta($job, $key, $default = null)
    {
        if (!array_key_exists('_meta', $job)) {
            return $default;
        }

        return ArrayHelper::getValue($job['_meta'], $key, $default);
    }
}
