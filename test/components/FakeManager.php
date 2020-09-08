<?php

namespace lav45\activityLogger\test\components;

use lav45\activityLogger\LogMessageDTO;
use lav45\activityLogger\Manager;

/**
 * Class FakeManager
 * @package lav45\activityLogger\test\models
 */
class FakeManager extends Manager
{
    /**
     * @var LogMessageDTO
     */
    public $message;

    /**
     * @param LogMessageDTO $message
     * @return bool
     */
    public function log(LogMessageDTO $message)
    {
        $this->message = $message;
        return true;
    }
}