<?php

namespace lav45\activityLogger\test\components;

use lav45\activityLogger\DeleteCommand;
use lav45\activityLogger\MessageData;
use lav45\activityLogger\StorageInterface;

/**
 * Class FakeStorage
 * @package lav45\activityLogger\test\components
 */
class FakeStorage implements StorageInterface
{
    public ?MessageData $message = null;

    public ?DeleteCommand $command = null;

    public function save(MessageData $message): void
    {
        $this->message = $message;
    }

    public function delete(DeleteCommand $command): void
    {
        $this->command = $command;
    }
}