<?php

namespace lav45\activityLogger\test\components;

use lav45\activityLogger\storage\DeleteCommand;
use lav45\activityLogger\storage\MessageData;
use lav45\activityLogger\storage\StorageInterface;

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