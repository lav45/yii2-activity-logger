<?php
/**
 * @link https://github.com/LAV45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

use lav45\activityLogger\modules\models\ActivityLog;
use yii\base\Behavior;
use yii\base\InvalidValueException;
use yii\db\ActiveRecord;
use yii\db\ArrayExpression;
use yii\db\JsonExpression;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * Class ActiveLogBehavior
 * @package lav45\activityLogger
 *
 * ======================= Example usage ======================
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
 *          [
 *              'class' => 'lav45\activityLogger\ActiveLogBehavior',
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
class ActiveLogBehavior extends Behavior
{
    use ManagerTrait;

    /**
     * @event MessageEvent an event that is triggered before inserting a record.
     * You may added in to the [[MessageEvent::append]] your custom log message.
     * @since 1.5.3
     */
    const EVENT_BEFORE_SAVE_MESSAGE = 'beforeSaveMessage';
    /**
     * @event Event an event that is triggered after inserting a record.
     * @since 1.5.3
     */
    const EVENT_AFTER_SAVE_MESSAGE = 'afterSaveMessage';

    /**
     * @var bool
     */
    public $softDelete = false;
    /**
     * @var \Closure
     * @since 1.6.0
     */
    public $beforeSaveMessage;
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
     */
    public $identicalAttributes = false;
    /**
     * @var \Closure a PHP callable that replaces the default implementation of [[isEmpty()]].
     * @since 1.5.2
     */
    public $isEmpty;
    /**
     * @var \Closure|array|string custom method to getEntityName
     * the callback function must return a string
     */
    public $getEntityName;
    /**
     * @var \Closure|array|string custom method to getEntityId
     * the callback function can return a string or array
     */
    public $getEntityId;
    /**
     * @var string
     * @since 1.7.0
     */
    public $actionCreate = ActivityLog::ACTION_CREATE;
    /**
     * @var string
     * @since 1.7.0
     */
    public $actionUpdate = ActivityLog::ACTION_UPDATE;
    /**
     * @var string
     * @since 1.7.0
     */
    public $actionDelete = ActivityLog::ACTION_DELETE;

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
    private $action;

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
        if (false === $this->getLogger()->enabled) {
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
        $this->action = $this->owner->getIsNewRecord() ? $this->actionCreate : $this->actionUpdate;
    }

    public function afterSave()
    {
        if (empty($this->changedAttributes)) {
            return;
        }
        $this->saveMessage($this->action, $this->changedAttributes);
    }

    public function beforeDelete()
    {
        if (false === $this->softDelete) {
            $this->getLogger()->delete($this->getEntityName(), $this->getEntityId());
        }
        $this->saveMessage($this->actionDelete, $this->prepareChangedAttributes(true));
    }

    /**
     * @param bool $unset
     * @return array
     */
    private function prepareChangedAttributes($unset = false)
    {
        $result = [];
        foreach ($this->attributes as $attribute => $options) {
            $old = $this->owner->getOldAttribute($attribute);
            $new = false === $unset ? $this->owner->getAttribute($attribute) : null;

            if ($this->isEmpty($old) && $this->isEmpty($new)) {
                continue;
            }
            if (false === $unset && false === $this->isAttributeChanged($attribute)) {
                continue;
            }
            $result[$attribute] = $this->resolveStoreValues($old, $new, $options);
        }
        return $result;
    }

    /**
     * @param string $attribute
     * @return bool
     */
    private function isAttributeChanged($attribute)
    {
        return $this->owner->isAttributeChanged($attribute, $this->identicalAttributes);
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
        if ($old_id instanceof ArrayExpression || $old_id instanceof JsonExpression) {
            $old_id = $old_id->getValue();
        }

        if ($new_id instanceof ArrayExpression || $new_id instanceof JsonExpression) {
            $new_id = $new_id->getValue();
        }

        return [
            'old' => ['value' => $old_id],
            'new' => ['value' => $new_id],
        ];
    }

    /**
     * @param string|int|array $old_id
     * @param string|int|array $new_id
     * @param string $listName
     * @return array
     */
    private function resolveListValues($old_id, $new_id, $listName)
    {
        $old['id'] = $old_id;
        $new['id'] = $new_id;
        $list = [];

        if (is_array($old_id) || is_array($new_id)) {
            $list = ArrayHelper::getValue($this->owner, $listName);
        }
        if (is_array($old_id)) {
            $old['value'] = array_intersect_key($list, array_flip($old_id));
        } else {
            $old['value'] = ArrayHelper::getValue($this->owner, [$listName, $old_id]);
        }
        if (is_array($new_id)) {
            $new['value'] = array_intersect_key($list, array_flip($new_id));
        } else {
            $new['value'] = ArrayHelper::getValue($this->owner, [$listName, $new_id]);
        }

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
        $data = $this->beforeSaveMessage($data);
        $this->addLog($data, $action);
        $this->afterSaveMessage();
    }

    /**
     * @param string|array $message
     * @param string|null $action
     * @return bool
     * @since 1.7.0
     */
    public function addLog($message, $action = null)
    {
        return $this->getLogger()->log($this->getEntityName(), $message, $action, $this->getEntityId());
    }

    /**
     * @param array $data
     * @return array
     * @since 1.5.3
     */
    public function beforeSaveMessage($data)
    {
        $name = self::EVENT_BEFORE_SAVE_MESSAGE;

        if (null !== $this->beforeSaveMessage) {
            return call_user_func($this->beforeSaveMessage, $data);
        }
        if (method_exists($this->owner, $name)) {
            return $this->owner->$name($data);
        }

        $event = new MessageEvent();
        $event->logData = $data;
        $this->owner->trigger($name, $event);
        return $event->logData;
    }

    /**
     * @since 1.5.3
     */
    public function afterSaveMessage()
    {
        $name = self::EVENT_AFTER_SAVE_MESSAGE;

        if (method_exists($this->owner, $name)) {
            $this->owner->$name();
        } else {
            $this->owner->trigger($name);
        }
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        if (null !== $this->getEntityName) {
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
        if (null === $this->getEntityId) {
            $result = $this->owner->getPrimaryKey();
        } else {
            $result = call_user_func($this->getEntityId);
        }
        if (empty($result)) {
            throw new InvalidValueException('the property "entityId" can not be empty');
        }
        if (is_array($result)) {
            ksort($result);
            $result = json_encode($result, 320);
        }
        return $result;
    }

    /**
     * Checks if the given value is empty.
     * A value is considered empty if it is null, an empty array, or an empty string.
     * Note that this method is different from PHP empty(). It will return false when the value is 0.
     * @param mixed $value the value to be checked
     * @return bool whether the value is empty
     * @since 1.5.2
     */
    public function isEmpty($value)
    {
        if (null !== $this->isEmpty) {
            return call_user_func($this->isEmpty, $value);
        }
        return null === $value || $value === '';
    }
}