<?php

namespace lav45\activityLogger;

/**
 * Class LogCollection
 * @package lav45\activityLogger
 */
class LogCollection
{
    /**
     * @var Manager
     */
    private $logger;
    /**
     * @var string
     */
    private $entityName;
    /**
     * @var string|int
     */
    private $entityId;
    /**
     * @var string
     */
    private $action;
    /**
     * @var string[]
     */
    private $messages = [];

    /**
     * LogCollection constructor.
     * @param Manager $logger
     * @param string $entityName
     */
    public function __construct($logger, $entityName)
    {
        $this->logger = $logger;
        $this->entityName = $entityName;
    }

    /**
     * @param string|int $value
     * @return $this
     */
    public function setEntityId($value)
    {
        $this->entityId = $value;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setAction($value)
    {
        $this->action = $value;
        return $this;
    }

    /**
     * @param string $value
     */
    public function addMessage($value)
    {
        $this->messages[] = $value;
    }

    /**
     * @return string[]
     */
    private function removeMessages()
    {
        $messages = $this->messages;
        $this->messages = [];
        return $messages;
    }

    /**
     * @return bool
     */
    public function push()
    {
        $messages = $this->removeMessages();
        if (empty($messages)) {
            return false;
        }

        return $this->logger->log(
            $this->entityName,
            $messages,
            $this->action,
            $this->entityId
        );
    }
}