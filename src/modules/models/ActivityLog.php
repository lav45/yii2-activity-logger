<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\modules\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Class ActivityLogger
 * @package lav45\activityLogger
 *
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
     * @var string
     * @since 1.7.0
     */
    public static $db;
    /**
     * @var string
     * @since 1.7.0
     */
    public static $tableName = '{{%activity_log}}';

    /**
     * @return string the table name
     */
    public static function tableName()
    {
        return static::$tableName ?: parent::tableName();
    }

    /**
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        if (static::$db) {
            return Yii::$app->get(static::$db);
        }
        return parent::getDb();
    }

    /**
     * @return array
     */
    public function attributeLabels()
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

    /**
     * @return array
     */
    public function getData()
    {
        return json_decode($this->data, true);
    }
}
