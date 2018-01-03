<?php

namespace lav45\activityLogger\modules\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Class ActivityLogger
 * @package lav45\activityLogger
 *
 * @property string $entity_name
 * @property string $entity_id
 * @property string $user_id
 * @property string $user_name
 * @property integer $created_at
 * @property string $action
 * @property string $data
 */
class ActivityLog extends ActiveRecord
{
    /**
     * @return string the table name
     */
    public static function tableName()
    {
        return '{{%activity_log}}';
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'entity_name' => Yii::t('app', 'Entity name'),
            'entity_id' => Yii::t('app', 'Entity'),
            'user_id' => Yii::t('app', 'User'),
            'user_name' => Yii::t('app', 'User name'),
            'created_at' => Yii::t('app', 'Created'),
            'action' => Yii::t('app', 'Action'),
            'data' => Yii::t('app', 'Data'),
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