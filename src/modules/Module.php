<?php

namespace lav45\activityLogger\modules;

use Yii;
use yii\base\Model;

class Module extends \yii\base\Module
{
    /**
     * @var \Closure
     * Example:
     *
     * function($id) {
     *      return Url::toRoute(['/user/default/view', 'id' => $id]);
     * }
     */
    public $createUserUrl;
    /**
     * @var string[]|Model[] [ entityName => \namespace\to\Model\EntityClass ]
     */
    public $entityMap = [];

    /**
     * @param string $id
     * @return null|Model
     * @throws \yii\base\InvalidConfigException
     */
    public function getEntityObject($id)
    {
        if (!isset($this->entityMap[$id])) {
            return null;
        }
        if (!is_object($this->entityMap[$id])) {
            $this->entityMap[$id] = Yii::createObject($this->entityMap[$id]);
        }
        return $this->entityMap[$id];
    }
}