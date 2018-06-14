<?php

namespace lav45\activityLogger;

use yii\base\Event;

/**
 * Class MessageEvent
 * @package lav45\activityLogger
 * @since 1.5.3
 */
class MessageEvent extends Event
{
    /**
     * @var array property to store data that will be recorded in the history of logs
     */
    public $logData = [];
}