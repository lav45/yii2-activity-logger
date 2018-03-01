<?php

namespace lav45\activityLogger\test\models;

use yii\db\ActiveRecord;
use lav45\activityLogger\ActiveRecordBehavior as ActivityLoggerBehavior;

/**
 * Class TestEntityName
 * @package lav45\activityLogger\test\models
 * @mixin ActivityLoggerBehavior
 */
class TestEntityName extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'logger' => [
                'class' => ActivityLoggerBehavior::class,
            ],
        ];
    }
}