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
    /** @var string[] */
    private $logs = [];

    /**
     * @param LogMessageDTO $message
     * @return bool
     */
    public function log(LogMessageDTO $message)
    {
        $this->logs[] = [
            'entityName' => $message->entityName,
            'entityId' => $message->entityId,
            'message' => $message->data,
            'action' => $message->action,
        ];
        return true;
    }

    /**
     * @return array
     */
    public function removeLogs()
    {
        $data = $this->logs;
        $this->logs = [];
        return $data;
    }
}