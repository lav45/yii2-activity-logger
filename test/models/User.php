<?php

namespace lav45\activityLogger\test\models;

use yii\db\ActiveRecord;
use lav45\activityLogger\modules\models\ActivityLog;
use lav45\activityLogger\ActiveLogBehavior;

/**
 * Class News
 * @package lav45\activityLogger\test\models
 *
 * @property int $id
 * @property string $login
 * @property boolean $is_hidden
 * @property int $friend_count
 * @property float $salary
 * @property string $birthday
 * @property int $status
 * @property int $company_id
 * @property string $_array_status internal json data
 *
 * @property array $arrayStatus
 *
 * @property ActivityLog[] $activityLogs
 * @property Company $company
 *
 * @mixin ActiveLogBehavior
 */
class User extends ActiveRecord
{
    const STATUS_ACTIVE = 10;
    const STATUS_DISABLED = 1;
    const STATUS_DRAFT = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        return [
            ActiveRecord::SCENARIO_DEFAULT => ActiveRecord::OP_ALL,
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['login'], 'string'],

            [['is_hidden'], 'default', 'value' => false],
            [['is_hidden'], 'boolean'],

            [['friend_count'], 'integer'],

            [['salary'], 'number'],

            [['birthday'], 'date', 'format' => 'dd.MM.yyyy'],

            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'integer'],
            [['status'], 'in', 'range' => array_keys($this->getStatusList())],

            [['arrayStatus'], 'safe'],

            [['company_id'], 'integer'],
            [['company_id'], 'exist',
                'targetRelation' => 'company',
                'targetAttribute' => 'id'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'logger' => [
                'class' => ActiveLogBehavior::class,
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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'login' => 'Login',
            'is_hidden' => 'Hidden',
            'friend_count' => 'Friends',
            'salary' => 'Salary',
            'birthday' => 'Birthday',
            'status' => 'Status',
            'arrayStatus' => 'Array status',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getOldAttribute($name)
    {
        if ($name === 'arrayStatus') {
            return json_decode(parent::getOldAttribute('_array_status'), true);
        }

        return parent::getOldAttribute($name);
    }

    /**
     * @inheritdoc
     */
    public function getAttribute($name)
    {
        if ($name === 'arrayStatus') {
            return $this->getArrayStatus();
        }

        return parent::getAttribute($name);
    }

    /**
     * @inheritdoc
     */
    public function isAttributeChanged($name, $identical = true)
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

    /**
     * @return array
     */
    public function getStatusList()
    {
        return [
            static::STATUS_ACTIVE => 'Active',
            static::STATUS_DISABLED => 'Disabled',
            static::STATUS_DRAFT => 'Draft',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActivityLogs()
    {
        return $this->hasMany(ActivityLog::class, [
            'entity_name' => 'entityName',
            'entity_id' => 'entityId',
        ]);
    }

    /**
     * @return null|ActivityLog
     */
    public function getLastActivityLog()
    {
        $query = $this->getActivityLogs();
        $query->multiple = false;

        return $query
            ->orderBy(['id' => SORT_DESC])
            ->one();
    }
}