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
        $options = array_filter([
            'entity_name' => $message->entityName,
            'entity_id' => $message->entityId,
            'created_at' => $message->createdAt,
            'user_id' => $message->userId,
            'user_name' => $message->userName,
            'action' => $message->action,
            'env' => $message->env,
            'data' => $message->data,
        ]);

        return (new Query)
            ->createCommand($this->db)
            ->insert($this->tableName, $options)
            ->execute();
    }

    /**
     * @param LogMessage $message
     * @return int
     */
    public function delete($message)
    {
        $condition = array_filter([
            'entity_name' => $message->entityName,
            'entity_id' => $message->entityId,
            'user_id' => $message->userId,
            'action' => $message->action,
            'env' => $message->env,
        ]);

        if ($date = $message->createdAt) {
            $condition = ['and', $condition, ['<=', 'created_at', $date]];
        }

        return (new Query)
            ->createCommand($this->db)
            ->delete($this->tableName, $condition)
            ->execute();
    }
}
