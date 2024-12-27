<?php

namespace lav45\activityLogger\test\models;

class UserEventMethod extends LogUser
{
    public array $appendLogs = [];

    public bool $afterSaveFlag = false;

    public bool $beforeSaveFlag = false;

    public function beforeSaveMessage($data): array
    {
        $this->beforeSaveFlag = true;
        return array_merge($data, $this->appendLogs);
    }

    public function afterSaveMessage(): void
    {
        $this->afterSaveFlag = true;
    }
}