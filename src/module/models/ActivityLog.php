<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\module\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Connection;

/**
 * @property int $id
 * @property string $entity_name
 * @property string $entity_id
 * @property string $user_id
 * @property string $user_name
 * @property integer $created_at
 * @property string $action
 * @property string $env
 * @property string $data
 */
class ActivityLog extends ActiveRecord
{
    /**
     * @since 1.7.0
     */
    public static ?string $db = null;
    /**
     * @since 1.7.0
     */
    public static string $tableName = '{{%activity_log}}';

    public static function tableName(): string
    {
        return static::$tableName ?: parent::tableName();
    }

    public static function getDb(): Connection
    {
        if (static::$db) {
            $db = Yii::$app->get(static::$db);
            if ($db instanceof Connection) {
                return $db;
            }
            throw new InvalidConfigException('Invalid db connection');
        }
        return parent::getDb();
    }

    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('lav45/logger', 'ID'),
            'entity_name' => Yii::t('lav45/logger', 'Entity name'),
            'entity_id' => Yii::t('lav45/logger', 'Entity'),
            'user_id' => Yii::t('lav45/logger', 'User'),
            'user_name' => Yii::t('lav45/logger', 'User name'),
            'created_at' => Yii::t('lav45/logger', 'Created'),
            'action' => Yii::t('lav45/logger', 'Action'),
            'env' => Yii::t('lav45/logger', 'Environment'),
            'data' => Yii::t('lav45/logger', 'Data'),
        ];
    }

    public function getData(): array
    {
        if ($this->data) {
            return (array)json_decode($this->data, true, 512, JSON_THROW_ON_ERROR);
        }
        return [];
    }

    public function getDecorator(): ActivityLogDecorator
    {
        return Yii::createObject(ActivityLogDecorator::class, [$this]);
    }
}
