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
    /**
     * @return array<string, array{__class: class-string, ...}>
     */
    public function behaviors()
    {
        return [
            'logger' => [
                '__class' => ActiveLogBehavior::class,
            ],
        ];
    }
}