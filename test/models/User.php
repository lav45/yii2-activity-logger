<?php

namespace lav45\activityLogger\test\models;

use yii\db\ActiveRecord;
use lav45\activityLogger\modules\models\ActivityLog;
use lav45\activityLogger\ActiveRecordBehavior as ActivityLoggerBehavior;

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
 *
 * @property ActivityLog[] $activityLogs
 * @property Company $company
 *
 * @mixin ActivityLoggerBehavior
 */
class User extends ActiveRecord
{
    const STATUS_ACTIVE = 10;
    const STATUS_DISABLED = 1;

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

            [['birthday'], 'date'],

            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'integer'],
            [['status'], 'in', 'range' => array_keys($this->getStatusList())],

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
                'class' => ActivityLoggerBehavior::class,
                'actionLabels' => [
                    'create' => 'Создание',
                    'update' => 'Изменение',
                    'delete' => 'Удаление',
                ],
                'attributes' => [
                    'login',
                    'is_hidden',
                    'friend_count',
                    'salary',
                    'birthday',
                    'status' => [
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
        ];
    }

    /**
     * @return array
     */
    public function getStatusList()
    {
        return [
            static::STATUS_ACTIVE => 'Active',
            static::STATUS_DISABLED => 'Disabled',
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

        $model = $query
            ->orderBy(['id' => SORT_DESC])
            ->one();

        return $model;
    }
}