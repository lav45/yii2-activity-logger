<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

use lav45\activityLogger\storage\DeleteCommand;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
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
 *              '__class' => 'lav45\activityLogger\ActiveLogBehavior',
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
    /**
     * @event MessageEvent an event that is triggered before inserting a record.
     * You may add in to the [[MessageEvent::append]] your custom log message.
     * @since 1.5.3
     */
    public const EVENT_BEFORE_SAVE_MESSAGE = 'beforeSaveMessage';
    /**
     * @event Event an event that is triggered after inserting a record.
     * @since 1.5.3
     */
    public const EVENT_AFTER_SAVE_MESSAGE = 'afterSaveMessage';

    public bool $softDelete = false;
    /** @since 1.6.0 */
    public ?\Closure $beforeSaveMessage = null;
    /**
     * [
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
    public array $attributes = [];

    public bool $identicalAttributes = false;
    /**
     * A PHP callable that replaces the default implementation of [[isEmpty()]].
     * @since 1.5.2
     */
    public ?\Closure $isEmpty = null;
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
     * [
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
    private array $changedAttributes = [];

    private string $action;

    private ManagerInterface $logger;

    public function __construct(
        ManagerInterface $logger,
        array            $config = []
    )
    {
        $this->logger = $logger;
        parent::__construct($config);
    }

    public function init(): void
    {
        $this->initAttributes();
    }

    private function initAttributes(): void
    {
        foreach ($this->attributes as $key => $value) {
            if (is_int($key)) {
                unset($this->attributes[$key]);
                $this->attributes[$value] = [];
            }
        }
    }

    public function events(): array
    {
        if (false === $this->logger->isEnabled()) {
            return [];
        }
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => [$this, 'beforeSave'],
            ActiveRecord::EVENT_BEFORE_UPDATE => [$this, 'beforeSave'],
            ActiveRecord::EVENT_BEFORE_DELETE => [$this, 'beforeDelete'],
            ActiveRecord::EVENT_AFTER_INSERT => [$this, 'afterSave'],
            ActiveRecord::EVENT_AFTER_UPDATE => [$this, 'afterSave'],
        ];
    }

    public function beforeSave(): void
    {
        $this->changedAttributes = $this->prepareChangedAttributes();
        $this->action = $this->owner->getIsNewRecord() ? 'created' : 'updated';
    }

    public function afterSave(): void
    {
        if (empty($this->changedAttributes)) {
            return;
        }
        $this->saveMessage($this->action, $this->changedAttributes);
    }

    public function beforeDelete(): void
    {
        if (false === $this->softDelete) {
            $this->deleteEntity();
        }
        $this->saveMessage('deleted', $this->prepareChangedAttributes(true));
    }

    private function prepareChangedAttributes(bool $unset = false): array
    {
        $result = [];
        foreach ($this->attributes as $attribute => $options) {
            $old = $this->owner->getOldAttribute($attribute);
            $new = false === $unset ? $this->owner->getAttribute($attribute) : null;

            if ($this->isEmpty($old) && $this->isEmpty($new)) {
                continue;
            }
            if (false === $unset && false === $this->owner->isAttributeChanged($attribute, $this->identicalAttributes)) {
                continue;
            }
            $result[$attribute] = $this->resolveStoreValues($old, $new, $options);
        }
        return $result;
    }

    /**
     * @param string|int|null $old_id
     * @param string|int|null $new_id
     */
    protected function resolveStoreValues($old_id, $new_id, array $options): array
    {
        if (isset($options['list'])) {
            $value = $this->resolveListValues($old_id, $new_id, $options['list']);
        } elseif (isset($options['relation'], $options['attribute'])) {
            $value = $this->resolveRelationValues($old_id, $new_id, $options['relation'], $options['attribute']);
        } else {
            $value = $this->resolveSimpleValues($old_id, $new_id);
        }
        return $value;
    }

    /**
     * @param string|int|null $old_id
     * @param string|int|null $new_id
     */
    private function resolveSimpleValues($old_id, $new_id): array
    {
        return [
            'old' => ['value' => $old_id],
            'new' => ['value' => $new_id],
        ];
    }

    /**
     * @param string|int|array|null $old_id
     * @param string|int|array|null $new_id
     */
    private function resolveListValues($old_id, $new_id, string $listName): array
    {
        $old = $new = [];
        $old['id'] = $old_id;
        $new['id'] = $new_id;
        $list = [];

        if (is_array($old_id) || is_array($new_id)) {
            $list = ArrayHelper::getValue($this->owner, $listName);
        }
        if (is_array($old_id)) {
            $old['value'] = array_intersect_key($list, array_flip($old_id));
        } elseif ($old_id) {
            $old['value'] = ArrayHelper::getValue($this->owner, [$listName, $old_id]);
        } else {
            $old['value'] = null;
        }
        if (is_array($new_id)) {
            $new['value'] = array_intersect_key($list, array_flip($new_id));
        } elseif ($new_id) {
            $new['value'] = ArrayHelper::getValue($this->owner, [$listName, $new_id]);
        } else {
            $new['value'] = null;
        }
        return [
            'old' => $old,
            'new' => $new
        ];
    }

    /**
     * @param string|int|null $old_id
     * @param string|int|null $new_id
     */
    private function resolveRelationValues($old_id, $new_id, string $relation, string $attribute): array
    {
        $old = $new = [];
        $old['id'] = $old_id;
        $new['id'] = $new_id;

        $relationQuery = clone $this->owner->getRelation($relation);
        if (count($relationQuery->link) > 1) {
            throw new InvalidConfigException('Relation model can only be linked through one primary key.');
        }
        $relationQuery->primaryModel = null;
        $idAttribute = array_keys($relationQuery->link)[0];
        $targetId = array_filter([$old_id, $new_id]);

        $relationModels = $relationQuery
            ->where([$idAttribute => $targetId])
            ->indexBy($idAttribute)
            ->limit(count($targetId))
            ->all();

        if ($old_id) {
            $old['value'] = ArrayHelper::getValue($relationModels, [$old_id, $attribute]);
        } else {
            $old['value'] = null;
        }
        if ($new_id) {
            $new['value'] = ArrayHelper::getValue($relationModels, [$new_id, $attribute]);
        } else {
            $new['value'] = null;
        }
        return [
            'old' => $old,
            'new' => $new
        ];
    }

    protected function deleteEntity(): void
    {
        $this->logger->delete(new DeleteCommand([
            'entityName' => $this->getEntityName(),
            'entityId' => $this->getEntityId(),
        ]));
    }

    protected function saveMessage(string $action, array $data): void
    {
        $data = $this->beforeSaveMessage($data);
        $this->addLog($data, $action);
        $this->afterSaveMessage();
    }

    /**
     * @param string|array $data
     * @since 1.7.0
     */
    public function addLog($data, string $action = null): bool
    {
        $message = $this->logger->createMessageBuilder($this->getEntityName())
            ->withEntityId($this->getEntityId())
            ->withAction($action)
            ->withData($data)
            ->build(time());

        return $this->logger->log($message);
    }

    /**
     * @since 1.5.3
     */
    public function beforeSaveMessage(array $data): array
    {
        if (null !== $this->beforeSaveMessage) {
            return call_user_func($this->beforeSaveMessage, $data);
        }
        $name = self::EVENT_BEFORE_SAVE_MESSAGE;
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
    public function afterSaveMessage(): void
    {
        $name = self::EVENT_AFTER_SAVE_MESSAGE;
        if (method_exists($this->owner, $name)) {
            $this->owner->$name();
        } else {
            $this->owner->trigger($name);
        }
    }

    public function getEntityName(): string
    {
        if (is_string($this->getEntityName)) {
            return $this->getEntityName;
        }
        if (is_callable($this->getEntityName)) {
            return call_user_func($this->getEntityName);
        }
        $class = get_class($this->owner);
        $class = StringHelper::basename($class);
        $this->getEntityName = Inflector::camel2id($class, '_');
        return $this->getEntityName;
    }

    public function getEntityId(): string
    {
        if (null === $this->getEntityId) {
            $result = $this->owner->getPrimaryKey();
        } elseif (is_callable($this->getEntityId)) {
            $result = call_user_func($this->getEntityId);
        } else {
            $result = $this->getEntityId;
        }
        if ($this->isEmpty($result)) {
            throw new InvalidValueException('the property "entityId" can not be empty');
        }
        if (is_array($result)) {
            ksort($result);
            $result = json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
    public function isEmpty($value): bool
    {
        if (null !== $this->isEmpty) {
            return call_user_func($this->isEmpty, $value);
        }
        return null === $value || '' === $value || [] === $value;
    }
}