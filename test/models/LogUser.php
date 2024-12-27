<?php

namespace lav45\activityLogger\test\models;

use lav45\activityLogger\ActiveLogBehavior;
use yii\base\Model;
use yii\db\ActiveQuery;

/**
 * Class LogUser
 * @package lav45\activityLogger\test\models
 */
class LogUser extends User
{
    public function behaviors(): array
    {
        return [
            'logger' => [
                '__class' => ActiveLogBehavior::class,
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
                    'fail_relation' => [
                        'relation' => 'failRelation',
                        'attribute' => 'name',
                    ],
                    'fail_link' => [
                        'relation' => 'failLink',
                        'attribute' => 'name',
                    ],
                ],
            ],
        ];
    }

    public function getFailLink(): ActiveQuery
    {
        return $this->hasOne(Company::class, [
            'id' => 'company_id',
            'name' => 'login',
        ]);
    }

    public function getFailRelation(): Model
    {
        return new Model();
    }

    public function getOldAttribute($name)
    {
        if ($name === 'arrayStatus') {
            $value = parent::getOldAttribute('_array_status');
            if ($value) {
                return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            }
            return null;
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

    public function getArrayStatus(): ?array
    {
        if ($this->_array_status) {
            return json_decode($this->_array_status, true, 512, JSON_THROW_ON_ERROR);
        }
        return null;
    }

    public function setArrayStatus(array $data): void
    {
        $this->_array_status = json_encode($data, JSON_THROW_ON_ERROR);
    }
}