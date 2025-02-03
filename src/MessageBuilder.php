<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

use lav45\activityLogger\storage\MessageData;

final class MessageBuilder implements MessageBuilderInterface
{
    private MessageData $message;

    public function __construct(string $entityName)
    {
        $this->message = new MessageData();
        $this->message->entityName = $entityName;
    }

    /**
     * @param string|int $id
     */
    public function withEntityId($id): self
    {
        $new = clone $this;
        $new->message->entityId = (string)$id;
        return $new;
    }

    public function withUserId(string $id): self
    {
        $new = clone $this;
        $new->message->userId = $id;
        return $new;
    }

    public function withUserName(string $name): self
    {
        $new = clone $this;
        $new->message->userName = $name;
        return $new;
    }

    public function withAction(string|null $action): self
    {
        $new = clone $this;
        $new->message->action = $action;
        return $new;
    }

    public function withEnv(string $env): self
    {
        $new = clone $this;
        $new->message->env = $env;
        return $new;
    }

    /**
     * @param array|string $data
     */
    public function withData($data): self
    {
        $new = clone $this;
        $new->message->data = $data;
        return $new;
    }

    public function build(int $now): MessageData
    {
        $this->message->createdAt = $now;
        return $this->message;
    }

    public function __clone()
    {
        $this->message = clone $this->message;
    }
}