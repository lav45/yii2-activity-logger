<?php

namespace lav45\activityLogger\modules\models;

use Yii;
use yii\helpers\Url;
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
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'entityName' => Yii::t('app', 'Entity name'),
            'userName' => Yii::t('app', 'User name'),
        ]);
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        $name = $this->entity_name;
        if ($this->entity_id) {
            $name .= ':' . $this->entity_id;
        }
        $url = Url::current([
            'entityName' => $this->entity_name,
            'entityId' => $this->entity_id,
            'page' => null
        ]);
        return '[' . Html::a($name , $url) . ']';
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        $name = Html::encode($this->user_name);
        $url = Url::current([
            'userId' => $this->user_id,
            'page' => null
        ]);
        return Html::a($name, $url);
    }

    /**
     * @return \Generator|DataModel[]|array
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