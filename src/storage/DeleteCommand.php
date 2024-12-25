<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\storage;

use yii\base\BaseObject;

final class DeleteCommand extends BaseObject
{
    public ?string $entityName = null;

    public ?string $entityId = null;

    public ?string $userId = null;

    public ?string $action = null;

    public ?string $env = null;

    public ?string $oldThan = null;
}