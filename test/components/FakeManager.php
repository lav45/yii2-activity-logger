<?php

namespace lav45\activityLogger\test\components;

use lav45\activityLogger\storage\MessageData;
use lav45\activityLogger\Manager;

class FakeManager extends Manager
{
    public MessageData $message;

    public function log(MessageData $message): bool
    {
        $this->message = $message;
        return true;
    }
}