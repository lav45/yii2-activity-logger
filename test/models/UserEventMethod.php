<?php

namespace lav45\activityLogger\test\models;

class UserEventMethod extends LogUser
{
    public $appendLogs = [];

    public $afterSaveFlag = false;

    public $beforeSaveFlag = false;

    public function beforeSaveMessage($data)
    {
        $this->beforeSaveFlag = true;
        return array_merge($data, $this->appendLogs);
    }

    public function afterSaveMessage()
    {
        $this->afterSaveFlag = true;
    }
}