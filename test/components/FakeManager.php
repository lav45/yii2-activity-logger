<?php

namespace lav45\activityLogger\test\components;

use lav45\activityLogger\ManagerInterface;
use lav45\activityLogger\storage\DeleteCommand;
use lav45\activityLogger\storage\MessageData;

class FakeManager implements ManagerInterface
{
    public MessageData $message;

    public function log(MessageData $message): bool
    {
        $this->message = $message;
        return true;
    }

    public function delete(DeleteCommand $command): bool
    {
        return true;
    }

    public function isEnabled(): bool
    {
        return true;
    }
}