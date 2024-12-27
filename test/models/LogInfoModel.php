<?php

namespace lav45\activityLogger\test\models;

use lav45\activityLogger\LogInfoBehavior;
use yii\base\Model;

class LogInfoModel extends Model
{
    public string $username = 'David';

    public function getProfile(): array
    {
        return [
            'email' => 'david@gmail.com'
        ];
    }

    public function behaviors(): array
    {
        return [
            'logInfo' => [
                '__class' => LogInfoBehavior::class,
            ]
        ];
    }
}