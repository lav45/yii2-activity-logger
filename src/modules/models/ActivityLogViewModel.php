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
     * @var DataModel|string|array
     */
    public $dataModel = DataModel::class;
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
     * @return null|\yii\base\Model
     */
    protected function getEntityModel()
    {
        return self::$module->getEntityObject($this->entity_name);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'entityName' => Yii::t('lav45/logger', 'Entity name'),
            'userName' => Yii::t('lav45/logger', 'User name'),
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
        return '[' . Html::a($name, $url) . ']';
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
     * @return string
     * @since 1.5.2
     */
    public function getEnv()
    {
        $env = Html::encode($this->env);
        $url = Url::current([
            'env' => $this->env,
            'page' => null
        ]);
        return Html::a($env, $url);
    }

    /**
     * @return \Generator|DataModel[]
     */
    public function getData()
    {
        foreach (parent::getData() as $attribute => $values) {
            if (is_string($values)) {
                $label = is_string($attribute) ? $this->getEntityAttributeLabel($attribute) : $attribute;
                yield $label => Html::encode(Yii::t('lav45/logger', $values));
            } else {
                $dataModel = $this->getDataModel()
                    ->setFormat($this->getAttributeFormat($attribute))
                    ->setData($values);

                yield $this->getEntityAttributeLabel($attribute) => $dataModel;
            }
        }
    }

    /**
     * @return DataModel
     */
    protected function getDataModel()
    {
        if (!is_object($this->dataModel)) {
            $this->dataModel = Yii::createObject($this->dataModel);
        }
        return $this->dataModel;
    }

    /**
     * @param string $attribute
     * @return string
     */
    protected function getEntityAttributeLabel($attribute)
    {
        if ($entityModel = $this->getEntityModel()) {
            return $entityModel->getAttributeLabel($attribute);
        }
        return $this->generateAttributeLabel($attribute);
    }

    /**
     * @return array
     */
    protected function getEntityAttributeFormats()
    {
        $entityModel = $this->getEntityModel();
        if (method_exists($entityModel, 'attributeFormats')) {
            return $entityModel->attributeFormats();
        }
        return [];
    }

    /**
     * @param string $attribute
     * @return string|null
     */
    protected function getAttributeFormat($attribute)
    {
        $formats = $this->getEntityAttributeFormats();
        return isset($formats[$attribute]) ? $formats[$attribute] : null;
    }
}
