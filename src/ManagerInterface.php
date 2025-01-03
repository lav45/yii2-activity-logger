<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

use lav45\activityLogger\storage\DeleteCommand;
use lav45\activityLogger\storage\MessageData;

interface ManagerInterface
{
    public function createMessageBuilder(string $entityName): MessageBuilderInterface;

    public function log(MessageData $message): bool;

    public function delete(DeleteCommand $command): bool;
}