<?php
/**
 * @link https://github.com/LAV45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

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
     * @param LogMessageDTO $message
     */
    public function save(LogMessageDTO $message)
    {
        (new Query)
            ->createCommand($this->db)
            ->insert($this->tableName, [
                'entity_name' => $message->entityName,
                'entity_id' => $message->entityId,
                'created_at' => $message->createdAt,
                'user_id' => $message->userId,
                'user_name' => $message->userName,
                'action' => $message->action,
                'env' => $message->env,
                'data' => $this->encode($message->data),
            ])
            ->execute();
    }

    /**
     * @param array|string $data
     * @return string
     */
    private function encode($data)
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param LogMessageDTO $message
     * @param int|null $old_than
     */
    public function delete(LogMessageDTO $message, $old_than = null)
    {
        $condition = array_filter([
            'entity_name' => $message->entityName,
            'entity_id' => $message->entityId,
            'user_id' => $message->userId,
            'action' => $message->action,
            'env' => $message->env,
        ]);

        if ($old_than) {
            $condition = ['and', $condition, ['<=', 'created_at', $old_than]];
        }

        (new Query)
            ->createCommand($this->db)
            ->delete($this->tableName, $condition)
            ->execute();
    }
}
