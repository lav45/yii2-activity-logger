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
    public static function setModule(Module $module)
    {
        static::$module = $module;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        $user_name = Html::encode($this->user_name);

        if (self::$module->createUserUrl === null) {
            return Html::tag('span', $user_name);
        }

        $url = call_user_func(self::$module->createUserUrl, $this->user_id);
        return Html::a($user_name, $url, [
            'target' => '_blank',
            'data-pjax' => 0
        ]);
    }

    /**
     * @return \Generator|DataModel[]
     * @throws \yii\base\InvalidConfigException
     */
    public function getData()
    {
        foreach ((array)parent::getData() as $attribute => $values) {
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