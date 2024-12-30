<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\storage;

final class ArrayStorage implements StorageInterface
{
    /** @var MessageData[] */
    public array $messages = [];
    /** @var DeleteCommand[] */
    public array $commands = [];

    public function save(MessageData $message): void
    {
        $this->messages[] = $message;
    }

    public function delete(DeleteCommand $command): void
    {
        $this->commands[] = $command;
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
}