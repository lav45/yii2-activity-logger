<?php
/**
 * @link https://github.com/LAV45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

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
    public function __construct(Manager $logger, $entityName)
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

        return $this->logger->log(new LogMessageDTO([
            'entityName' => $this->entityName,
            'entityId' => $this->entityId,
            'action' => $this->action,
            'data' => $messages,
        ]));
    }
}