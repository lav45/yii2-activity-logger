<?php

namespace lav45\activityLogger;

use yii\db\Query;
use yii\db\Connection;
use yii\di\Instance;
use yii\base\BaseObject;

/**
 * Class DbStorage
 * @package repository
 */
class DbStorage extends BaseObject implements StorageInterface
{
    /**
     * @var Connection|string|array
     */
    public $db = 'db';
    /**
     * @var string
     */
    public $tableName = '{{%activity_log}}';

    /**
     * Initializes the object.
     */
    public function init()
    {
        $this->db = Instance::ensure($this->db, Connection::class);
    }

    /**
     * @param LogMessage $message
     * @return int
     */
    public function save($message)
    {
        return (new Query)
            ->createCommand($this->db)
            ->insert($this->tableName, [
                'entity_name' => $message->getEntityName(),
                'entity_id' => $message->getEntityId(),
                'created_at' => $message->getCreatedAt(),
                'user_id' => $message->getUserId(),
                'user_name' => $message->getUserName(),
                'action' => $message->getAction(),
                'data' => $message->getData(),
            ])
            ->execute();
    }

    /**
     * @param int $date
     * @return int
     */
    public function clean($date)
    {
        return $this->deleteByCondition(['<', 'created_at', $date]);
    }

    /**
     * @param string $entityName
     * @param string|null $entityId
     * @return int
     */
    public function delete($entityName, $entityId)
    {
        return $this->deleteByCondition([
            'entity_name' => $entityName,
            'entity_id' => $entityId,
        ]);
    }

    /**
     * @param array $condition
     * @return int
     */
    private function deleteByCondition($condition)
    {
        return (new Query)
            ->createCommand($this->db)
            ->delete($this->tableName, $condition)
            ->execute();
    }
}