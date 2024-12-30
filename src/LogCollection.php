<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

class LogCollection
{
    private ManagerInterface $logger;

    private MessageBuilderInterface $builder;
    /** @var string[] */
    private array $data = [];

    public function __construct(ManagerInterface $logger, string $entityName)
    {
        $this->logger = $logger;
        $this->builder = $logger->createMessageBuilder($entityName);
    }

    /**
     * @param string|int $value
     */
    public function setEntityId($value): self
    {
        $this->builder = $this->builder->withEntityId($value);
        return $this;
    }

    public function setAction(string $value): self
    {
        $this->builder = $this->builder->withAction($value);
        return $this;
    }

    public function addMessage(string $value): void
    {
        $this->data[] = $value;
    }

    /**
     * @return string[]
     */
    private function flushData(): array
    {
        $data = $this->data;
        $this->data = [];
        return $data;
    }

    public function push(): bool
    {
        $data = $this->flushData();
        if (empty($data)) {
            return false;
        }

        $message = $this->builder
            ->withData($data)
            ->build(time());

        return $this->logger->log($message);
    }
}