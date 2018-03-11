<?php

namespace lav45\activityLogger\test\components;

/**
 * Class FakeLogger
 * @package lav45\activityLogger\test\models
 */
class FakeLogger
{
    /**
     * @var string[]
     */
    private $logs = [];

    /**
     * @param string $entityName
     * @param string|array $message
     * @param null|string $action
     * @param null|string|int $entityId
     * @return bool
     */
    public function log($entityName, $message, $action = null, $entityId = null)
    {
        $this->logs[] = [
            'entityName' => $entityName,
            'entityId' => $entityId,
            'message' => $message,
            'action' => $action,
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