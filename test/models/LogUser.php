<?php

namespace lav45\activityLogger\test\models;

use lav45\activityLogger\ActiveLogBehavior;

/**
 * Class LogUser
 * @package lav45\activityLogger\test\models
 */
class LogUser extends User
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'logger' => [
                'class' => ActiveLogBehavior::class,
                'getEntityName' => 'user',
                'attributes' => [
                    'login',
                    'is_hidden',
                    'friend_count',
                    'salary',
                    'birthday',
                    'status' => [
                        'list' => 'statusList',
                    ],
                    'arrayStatus' => [
                        'list' => 'statusList',
                    ],
                    'company_id' => [
                        'relation' => 'company',
                        'attribute' => 'name',
                    ],
                ],
            ],
        ];
    }

    public function getOldAttribute($name)
    {
        if ($name === 'arrayStatus') {
            return json_decode(parent::getOldAttribute('_array_status'), true);
        }
        return parent::getOldAttribute($name);
    }

    public function getAttribute($name)
    {
        if ($name === 'arrayStatus') {
            return $this->getArrayStatus();
        }
        return parent::getAttribute($name);
    }

    public function isAttributeChanged($name, $identical = true): bool
    {
        if ($name === 'arrayStatus') {
            return $this->getOldAttribute('arrayStatus') !== $this->getAttribute('arrayStatus');
        }
        return parent::isAttributeChanged($name, $identical);
    }

    /**
     * @return array
     */
    public function getArrayStatus()
    {
        return json_decode($this->_array_status, true);
    }

    /**
     * @param array $data
     */
    public function setArrayStatus(array $data)
    {
        $this->_array_status = json_encode($data);
    }
}