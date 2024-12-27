<?php

namespace lav45\activityLogger\test\components;

use lav45\activityLogger\storage\DeleteCommand;
use lav45\activityLogger\storage\MessageData;
use lav45\activityLogger\storage\StorageInterface;

class ExceptionStorage implements StorageInterface
{
    public function save(MessageData $message): void
    {
        throw new \RuntimeException();
    }

    public function delete(DeleteCommand $command): void
    {
        throw new \RuntimeException();
    }
}