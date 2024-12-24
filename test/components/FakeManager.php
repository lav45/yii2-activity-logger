<?php

namespace lav45\activityLogger\test\components;

use lav45\activityLogger\MessageData;
use lav45\activityLogger\Manager;

/**
 * Class FakeManager
 * @package lav45\activityLogger\test\models
 */
class FakeManager extends Manager
{
    public MessageData $message;

    public function log(MessageData $message): bool
    {
        $this->message = $message;
        return true;
    }
}