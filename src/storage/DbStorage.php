<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\storage;

use yii\db\Query;
use yii\db\Connection;
use yii\di\Instance;
use yii\base\BaseObject;

class DbStorage extends BaseObject implements StorageInterface
{
    /** @var Connection|string|array */
    public $db = 'db';

    public string $tableName = '{{%activity_log}}';

    public function init(): void
    {
        $this->db = Instance::ensure($this->db, Connection::class);
    }

    public function save(MessageData $message): void
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
     */
    private function encode($data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function delete(DeleteCommand $command): void
    {
        $condition = array_filter([
            'entity_name' => $command->entityName,
            'entity_id' => $command->entityId,
            'user_id' => $command->userId,
            'action' => $command->action,
            'env' => $command->env,
        ]);

        if ($command->oldThan) {
            if (empty($condition)) {
                throw new \InvalidArgumentException("Condition can't be empty");
            }
            $condition = ['and', $condition, ['<=', 'created_at', $command->oldThan]];
        }

        (new Query)
            ->createCommand($this->db)
            ->delete($this->tableName, $condition)
            ->execute();
    }
}
