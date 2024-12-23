<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

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