<?php

namespace lav45\activityLogger\test\models;

use lav45\activityLogger\ActiveLogBehavior;
use lav45\activityLogger\middlewares\UserInterface;
use lav45\activityLogger\module\models\ActivityLog;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $login
 * @property boolean $is_hidden
 * @property int $friend_count
 * @property float $salary
 * @property string $birthday
 * @property int $status
 * @property int $company_id
 * @property string $_array_status internal json data
 * @property int $fail_relation
 * @property int $fail_link
 *
 * @property array $arrayStatus
 *
 * @property ActivityLog[] $activityLogs
 * @property Company $company
 *
 * @mixin ActiveLogBehavior
 */
class User extends ActiveRecord implements IdentityInterface, UserInterface
{
    public const STATUS_ACTIVE = 10;
    public const STATUS_DISABLED = 1;
    public const STATUS_DRAFT = 2;

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

        /** @var null|ActivityLog */
        return $query
            ->orderBy(['id' => SORT_DESC])
            ->one();
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey)
    {
        return false;
    }

    public function getName(): string
    {
        return $this->login;
    }
}