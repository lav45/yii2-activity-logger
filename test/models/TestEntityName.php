<?php

namespace lav45\activityLogger\test\models;

use yii\db\ActiveRecord;
use lav45\activityLogger\ActiveLogBehavior;

/**
 * Class TestEntityName
 * @package lav45\activityLogger\test\models
 * @mixin ActiveLogBehavior
 */
class TestEntityName extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'logger' => [
                'class' => ActiveLogBehavior::class,
            ],
        ];
    }
}