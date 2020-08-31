<?php

namespace lav45\activityLogger\test\components;

use lav45\activityLogger\LogMessageDTO;
use lav45\activityLogger\StorageInterface;

/**
 * Class FakeStorage
 * @package lav45\activityLogger\test\components
 */
class FakeStorage implements StorageInterface
{
    /** @var LogMessageDTO */
    public $message;
    /** @var int */
    public $old_than;

    public function save(LogMessageDTO $message)
    {
        $this->message = $message;
    }

    public function delete(LogMessageDTO $message, $old_than = null)
    {
        $this->message = $message;
        $this->old_than = $old_than;
    }
}