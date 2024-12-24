<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

use Yii;

/**
 * Class LogCollection
 * @package lav45\activityLogger
 */
class LogCollection
{
    private Manager $logger;

    private string $entityName;
    /** @var string|int|null */
    private $entityId;

    private ?string $action = null;
    /** @var string[] */
    private array $messages = [];

    public function __construct(Manager $logger, string $entityName)
    {
        $this->logger = $logger;
        $this->entityName = $entityName;
    }

    /**
     * @param string|int $value
     */
    public function setEntityId($value): self
    {
        $this->entityId = $value;
        return $this;
    }

    public function setAction(string $value): self
    {
        $this->action = $value;
        return $this;
    }

    public function addMessage(string $value): void
    {
        $this->messages[] = $value;
    }

    /**
     * @return string[]
     */
    private function flushMessages(): array
    {
        $messages = $this->messages;
        $this->messages = [];
        return $messages;
    }

    public function push(): bool
    {
        $messages = $this->flushMessages();
        if (empty($messages)) {
            return false;
        }

        /** @var MessageData $message */
        $message = Yii::createObject([
            'class' => MessageData::class,
            'entityName' => $this->entityName,
            'entityId' => $this->entityId,
            'action' => $this->action,
            'data' => $messages,
        ]);
        return $this->logger->log($message);
    }
}