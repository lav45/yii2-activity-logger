<?php

namespace lav45\activityLogger\test\components;

use lav45\activityLogger\ManagerInterface;
use lav45\activityLogger\MessageBuilder;
use lav45\activityLogger\MessageBuilderInterface;
use lav45\activityLogger\middlewares\Middleware;
use lav45\activityLogger\middlewares\MiddlewarePipeline;
use lav45\activityLogger\storage\DeleteCommand;
use lav45\activityLogger\storage\MessageData;

class FakeManager implements ManagerInterface
{
    public ?MessageData $message = null;

    /** @var Middleware[] */
    public array $middlewares = [];

    public function log(MessageData $message): bool
    {
        $this->message = $message;
        return true;
    }

    public function delete(DeleteCommand $command): bool
    {
        return true;
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function createMessageBuilder(string $entityName): MessageBuilderInterface
    {
        $pipeline = new MiddlewarePipeline(...$this->middlewares);
        $builder = new MessageBuilder($entityName);
        return $pipeline->handle($builder);
    }
}