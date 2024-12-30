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

class DummyManager implements ManagerInterface
{
    public function isEnabled(): bool
    {
        return false;
    }

    public function log(MessageData $message): bool
    {
        return false;
    }

    public function delete(DeleteCommand $command): bool
    {
        return false;
    }

    public function __get(string $name)
    {
        return null;
    }

    public function __set(string $name, $value): void
    {
    }

    public function __isset(string $name)
    {
        return false;
    }

    public function createMessageBuilder(string $entityName): MessageBuilderInterface
    {
        return new MessageBuilder($entityName);
    }
}