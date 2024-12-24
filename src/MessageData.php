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
 * Class MessageData this is a data transfer object
 * @package lav45\activityLogger
 */
final class MessageData extends BaseObject
{
    /** Alias name target object */
    public string $entityName;
    /** ID target object */
    public ?string $entityId = null;
    /** Creation date of the action */
    public int $createdAt;
    /** ID user who performed the action */
    public ?string $userId = null;
    /** UserName who performed the action */
    public ?string $userName = null;
    /** Action performed on the object */
    public ?string $action = null;
    /** Environment, which produced the effect */
    public ?string $env = null;
    /** @var array|string|null */
    public $data;
}
