<?php

namespace lav45\activityLogger;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\base\InvalidArgumentException;

/**
 * Class ActiveRecordBehavior
 * @package lav45\activityLogger\entity
 *
 * ======================= Example usage ======================
 *  // Recommended
 *  public function rules()
 *  {
 *      return [
 *          // If a field value is not required use `default` validator.
 *          // If a field is not filled, it will set its value to NULL.
 *
 *          [['parent_id'], 'integer'],
 *          [['parent_id'], 'default'],
 *
 *          [['comment'], 'string'],
 *          [['comment'], 'default'],
 *      ];
 *  }
 *
 *  // Recommended
 *  public function transactions()
 *  {
 *      return [
 *          ActiveRecord::SCENARIO_DEFAULT => ActiveRecord::OP_ALL,
 *      ];
 *  }
 *
 *  public function behaviors()
 *  {
 *      return [
 *          ['class' => 'yii\behaviors\AttributeTypecastBehavior'], // Recommended
 *          [
 *              'class' => 'lav45\activityLogger\ActiveRecordBehavior',
 *              'attributes' => [
 *                  // simple attribute
 *                  'title',
 *
 *                  // the value of the attribute is a item in the list
 *                  'status' => [
 *                      // => $this->getStatusList()
 *                      'list' => 'statusList'
 *                  ],
 *
 *                  // the attribute value is the [id] of the relation model
 *                  'owner_id' => [
 *                      'relation' => 'owner',
 *                      'attribute' => 'username',
 *                  ],
 *              ]
 *          ]
 *      ];
 *  }
 * ============================================================
 *
 * @property string $entityName
 * @property string $entityId
 * @property ActiveRecord $owner
 */
class ActiveRecordBehavior extends Behavior
{
    use ManagerTrait;
    /**
     * @var bool
     */
    public $softDelete = false;
    /**
     * @var array
     *  - create
     *  - update
     *  - delete
     */
    public $actionLabels = [
        'create' => 'created',
        'update' => 'updated',
        'delete' => 'removed',
    ];
    /**
     * @var array [
     *  // simple attribute
     *  'title',
     *
     *  // simple boolean attribute
     *  'is_publish',
     *
     *  // the value of the attribute is a item in the list
     *  // => $this->getStatusList()
     *  'status' => [
     *      'list' => 'statusList'
     *  ],
     *
     *  // the attribute value is the [id] of the relation model
     *  'owner_id' => [
     *      'relation' => 'user',
     *      'attribute' => 'username'
     *  ]
     * ]
     */
    public $attributes = [];
    /**
     * @var bool
     * @deprecated will be removed in 1.6 version
     */
    public $identicalAttributes = true;
    /**
     * @var \Closure|array|string|null custom method to getEntityName
     */
    public $getEntityName;
    /**
     * @var \Closure|array|string|null custom method to getEntityId
     */
    public $getEntityId;
    /**
     * @var array [
     *  'title' => [
     *      'new' => ['value' => 'New title'],
     *  ],
     *  'is_publish' => [
     *      'old' => ['value' => false],
     *      'new' => ['value' => true],
     *  ],
     *  'status' => [
     *      'old' => ['id' => 0, 'value' => 'Disabled'],
     *      'new' => ['id' => 1, 'value' => 'Active'],
     *  ],
     *  'owner_id' => [
     *      'old' => ['id' => 1, 'value' => 'admin'],
     *      'new' => ['id' => 2, 'value' => 'lucy'],
     *  ]
     * ]
     */
    private $changedAttributes = [];
    /**
     * @var string
     */
    private $actionName;

    /**
     * Initializes the object.
     */
    public function init()
    {
        $this->initAttributes();
    }

    private function initAttributes()
    {
        foreach ($this->attributes as $key => $value) {
            if (is_int($key)) {
                unset($this->attributes[$key]);
                $this->attributes[$value] = null;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        if ($this->getLogger()->enabled === false) {
            return [];
        }

        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
        ];
    }

    public function beforeSave()
    {
        $this->changedAttributes = $this->prepareChangedAttributes();
        $this->actionName = $this->getActionLabel($this->owner->getIsNewRecord() ? 'create' : 'update');
    }

    public function afterSave()
    {
        if (empty($this->changedAttributes)) {
            return;
        }

        $this->saveMessage($this->actionName, $this->changedAttributes);
    }

    public function beforeDelete()
    {
        if ($this->softDelete === false) {
            $this->getLogger()->delete($this->getEntityName(), $this->getEntityId());
        }

        $this->saveMessage($this->getActionLabel('delete'), $this->prepareChangedAttributes(true));
    }

    /**
     * @param string $label
     * @return string|null
     */
    private function getActionLabel($label)
    {
        return ArrayHelper::getValue($this->actionLabels, $label);
    }

    /**
     * @param bool $unset
     * @return array
     */
    private function prepareChangedAttributes($unset = false)
    {
        $result = [];
        foreach ($this->attributes as $attribute => $options) {
            if ($unset === false && $this->owner->isAttributeChanged($attribute, $this->identicalAttributes) === false) {
                continue;
            }

            $old = $this->owner->getOldAttribute($attribute);
            $new = $unset === false ? $this->owner->getAttribute($attribute) : null;

            $result[$attribute] = $this->resolveStoreValues($old, $new, $options);
        }
        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    private function filterStoreValues(array $data)
    {
        if (isset($data['old']) && !isset($data['old']['value'])) {
            unset($data['old']);
        }
        return $data;
    }

    /**
     * @param string|int $old_id
     * @param string|int $new_id
     * @param array $options
     * @return array
     */
    protected function resolveStoreValues($old_id, $new_id, $options)
    {
        if (isset($options['list'])) {
            $value = $this->resolveListValues($old_id, $new_id, $options['list']);
        } elseif (isset($options['relation'], $options['attribute'])) {
            $value = $this->resolveRelationValues($old_id, $new_id, $options['relation'], $options['attribute']);
        } else {
            $value = $this->resolveSimpleValues($old_id, $new_id);
        }
        return $this->filterStoreValues($value);
    }

    /**
     * @param string|int $old_id
     * @param string|int $new_id
     * @return array
     */
    private function resolveSimpleValues($old_id, $new_id)
    {
        return [
            'old' => ['value' => $old_id],
            'new' => ['value' => $new_id],
        ];
    }

    /**
     * @param string|int $old_id
     * @param string|int $new_id
     * @param string $listName
     * @return array
     */
    private function resolveListValues($old_id, $new_id, $listName)
    {
        $old['id'] = $old_id;
        $new['id'] = $new_id;

        $old['value'] = ArrayHelper::getValue($this->owner, [$listName, $old_id]);
        $new['value'] = ArrayHelper::getValue($this->owner, [$listName, $new_id]);

        return [
            'old' => $old,
            'new' => $new
        ];
    }

    /**
     * @param string|int $old_id
     * @param string|int $new_id
     * @param string $relation
     * @param string $attribute
     * @return array
     */
    private function resolveRelationValues($old_id, $new_id, $relation, $attribute)
    {
        $old['id'] = $old_id;
        $new['id'] = $new_id;

        $relationQuery = clone $this->owner->getRelation($relation);
        $relationQuery->primaryModel = null;
        $idAttribute = array_keys($relationQuery->link)[0];
        $targetId = array_filter([$old_id, $new_id]);

        $relationModels = $relationQuery
            ->where([$idAttribute => $targetId])
            ->indexBy($idAttribute)
            ->limit(count($targetId))
            ->all();

        $old['value'] = ArrayHelper::getValue($relationModels, [$old_id, $attribute]);
        $new['value'] = ArrayHelper::getValue($relationModels, [$new_id, $attribute]);

        return [
            'old' => $old,
            'new' => $new
        ];
    }

    /**
     * @param string $action
     * @param array $data
     */
    protected function saveMessage($action, array $data)
    {
        $this->getLogger()->log($this->getEntityName(), $data, $action, $this->getEntityId());
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        if ($this->getEntityName !== null) {
            return call_user_func($this->getEntityName);
        }

        $class = StringHelper::basename(get_class($this->owner));
        return Inflector::camel2id($class, '_');
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        if ($this->getEntityId !== null) {
            return call_user_func($this->getEntityId);
        }

        $result = $this->owner->getPrimaryKey();
        if (empty($result)) {
            throw new InvalidArgumentException();
        }
        if (is_array($result)) {
            ksort($result);
            return json_encode($result, 320);
        }
        return $result;
    }
}
