<?php

namespace trntv\bus\console;
use yii\base\Event;


/**
 * Class QueueBusEvent
 * @package trntv\bus\console
 * @author Eugene Terentev <eugene@terentev.net>
 */
class QueueBusEvent extends Event
{
    /**
     * @var array
     */
    public $job;
    /**
     * @var bool
     */
    public $isValid = true;
}