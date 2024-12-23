<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

use yii\base\BaseObject;

/**
 * Class LogMessageDTO this is a data transfer object
 * @package lav45\activityLogger
 */
class LogMessageDTO extends BaseObject
{
    /**
     * @var string alias name target object
     */
    public $entityName;
    /**
     * @var string id target object
     */
    public $entityId;
    /**
     * @var int creation date of the action
     */
    public $createdAt;
    /**
     * @var string id user who performed the action
     */
    public $userId;
    /**
     * @var string user name who performed the action
     */
    public $userName;
    /**
     * @var string the action performed on the object
     */
    public $action;
    /**
     * @var string environment, which produced the effect
     */
    public $env;
    /**
     * @var array|string
     */
    public $data;
}
