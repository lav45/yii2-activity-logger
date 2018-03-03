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
            'entity_name' => $message->getEntityName(),
            'entity_id' => $message->getEntityId(),
            'created_at' => $message->getCreatedAt(),
            'user_id' => $message->getUserId(),
            'user_name' => $message->getUserName(),
            'action' => $message->getAction(),
            'env' => $message->getEnv(),
            'data' => $message->getData(),
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
            'entity_name' => $message->getEntityName(),
            'entity_id' => $message->getEntityId(),
            'user_id' => $message->getUserId(),
            'action' => $message->getAction(),
            'env' => $message->getEnv(),
        ]);

        if ($date = $message->getCreatedAt()) {
            $condition = ['and', $condition, ['<=', 'created_at', $date]];
        }

        return (new Query)
            ->createCommand($this->db)
            ->delete($this->tableName, $condition)
            ->execute();
    }
}
