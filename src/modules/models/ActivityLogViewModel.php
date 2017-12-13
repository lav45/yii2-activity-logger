<?php

namespace lav45\activityLogger\modules\models;

use Yii;
use yii\helpers\Html;
use lav45\activityLogger\modules\Module;

/**
 * Class ActivityLogViewModel
 * @package lav45\activityLogger\modules\models
 */
class ActivityLogViewModel extends ActivityLog
{
    /**
     * @var Module
     */
    private static $module;

    /**
     * @param Module $module
     */
    public static function setModule($module)
    {
        static::$module = $module;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        $user_name = Html::encode($this->user_name);
        $url = ['index', 'userId' => $this->user_id];
        return Html::a($user_name, $url);
    }

    /**
     * @return \Generator|DataModel[]
     * @throws \yii\base\InvalidConfigException
     */
    public function getData()
    {
        foreach (parent::getData() as $attribute => $values) {
            if (is_int($attribute)) {
                yield $attribute => Html::encode(Yii::t('app', $values));
            } else {
                if ($entityModel = self::$module->getEntityObject($this->entity_name)) {
                    $attribute = $entityModel->getAttributeLabel($attribute);
                }
                yield $attribute => Yii::createObject(DataModel::class, [$values]);
            }
        }
    }
}