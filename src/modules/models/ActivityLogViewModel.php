<?php
/**
 * @link https://github.com/LAV45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\modules\models;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * Class ActivityLogViewModel
 * @package lav45\activityLogger\modules\models
 */
class ActivityLogViewModel extends ActivityLog
{
    /** @var DataModel|string|array */
    public $dataModel = DataModel::class;
    /** @var array [ entity_name => Entity::class ] */
    public $entityMap = [];
    /** @var array */
    private $entityModel = [];

    /**
     * @return \yii\base\Model|null
     */
    protected function getEntityModel()
    {
        if (isset($this->entityModel[$this->entity_name]) === false) {
            $this->entityModel[$this->entity_name] = $this->getEntityObject($this->entity_name);
        }
        return $this->entityModel[$this->entity_name] ?: null;
    }

    /**
     * @param string $id
     * @return false|\yii\base\Model
     */
    private function getEntityObject($id)
    {
        if (isset($this->entityMap[$id]) === false) {
            return false;
        }
        /** @var \yii\base\Model $class */
        $class = $this->entityMap[$id];
        return $class::instance();
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
        if (null !== $entityModel && method_exists($entityModel, 'attributeFormats')) {
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
