<?php

namespace lav45\activityLogger\test\models;

class UserEventMethod extends User
{
    public $appendLogs = [];

    public $afterSaveFlag = false;

    public $beforeSaveFlag = false;

    public function beforeSaveMessage()
    {
        $this->beforeSaveFlag = true;
        return $this->appendLogs;
    }

    public function afterSaveMessage()
    {
        $this->afterSaveFlag = true;
    }
}