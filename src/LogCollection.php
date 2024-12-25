<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

use lav45\activityLogger\storage\MessageData;
use Yii;

class LogCollection
{
    private Manager $logger;

    private MessageData $data;
    /** @var string[] */
    private array $messages = [];

    public function __construct(Manager $logger, string $entityName)
    {
        $this->logger = $logger;

        /** @var MessageData $data */
        $data = Yii::createObject([
            'class' => MessageData::class,
            'entityName' => $entityName,
        ]);
        $this->data = $data;
    }

    /**
     * @param string|int $value
     */
    public function setEntityId($value): self
    {
        $this->data->entityId = $value;
        return $this;
    }

    public function setAction(string $value): self
    {
        $this->data->action = $value;
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

        $data = clone $this->data;
        $data->createdAt = time();
        $data->data = $messages;

        return $this->logger->log($data);
    }
}