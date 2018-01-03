<?php

namespace lav45\activityLogger;

use Yii;
use yii\di\Instance;
use yii\base\BaseObject;

/**
 * Class Manager
 * @package lav45\activityLogger
 */
class Manager extends BaseObject
{
    /**
     * @var bool
     */
    public $enabled = true;
    /**
     * @var int|bool
     */
    public $deleteOldThanDays = 365;
    /**
     * @var string|null
     */
    public $user = 'user';
    /**
     * @var string
     */
    public $userNameAttribute = 'username';
    /**
     * @var string|array|StorageInterface
     */
    public $storage = DbStorage::class;
    /**
     * @var array
     */
    public $messageClass = [
        'class' => LogMessage::class
    ];
    /**
     * @var LogMessage
     */
    private $message;

    /**
     * Initializes the object.
     */
    public function init()
    {
        $this->initMessageOptions();
        $this->initStorage();
    }

    protected function initMessageOptions()
    {
        if ($this->user === null) {
            return;
        }
        if ($this->user = Yii::$app->get($this->user, false)) {
            /** @var \yii\web\IdentityInterface $identity */
            $identity = $this->user->identity;
            $this->messageClass = array_merge($this->messageClass, [
                'userId' => $identity->getId(),
                'userName' => $identity->{$this->userNameAttribute}
            ]);
        }
    }

    protected function initStorage()
    {
        $this->storage = Instance::ensure($this->storage, StorageInterface::class);
    }

    /**
     * @param string $entityName
     * @param string $messageText
     * @param null|string $action
     * @param null|string $entityId
     * @return bool
     */
    public function log($entityName, $messageText, $action = null, $entityId = null)
    {
        if (empty($entityName) || empty($messageText)) {
            return false;
        }
        return $this->createMessage($entityName, [
            'entityId' => $entityId,
            'data' => [$messageText],
            'action' => $action,
        ])->save();
    }

    /**
     * @param string $entityName
     * @param array $options
     *  - entityId
     *  - createdAt
     *  - userId
     *  - userName
     *  - action
     *  - data
     *
     * @return $this
     */
    public function createMessage($entityName, array $options)
    {
        if (empty($entityName) || empty($options)) {
            return $this;
        }
        $options = array_merge($this->messageClass, $options);
        $this->message = Yii::createObject($options, [$entityName]);
        return $this;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        if ($this->enabled === false) {
            return false;
        }
        if ($this->message === null) {
            return false;
        }

        $message = $this->message;
        $this->message = null;

        try {
            $result = $this->storage->save($message);
        } catch (\Exception $e) {
            if (YII_DEBUG) {
                throw $e;
            } else {
                return false;
            }
        }

        return $result > 0;
    }

    /**
     * @param string $entityName
     * @param string|null $entityId
     * @return bool
     */
    public function delete($entityName, $entityId = null)
    {
        return $this->storage->delete($entityName, $entityId) > 0;
    }

    /**
     * @return int|bool the number of deleted rows or false if clear range not set
     */
    public function clean()
    {
        if ($this->deleteOldThanDays === false) {
            return false;
        }

        $cutOffDate = time() - $this->deleteOldThanDays * 86400;
        $amountDeleted = $this->storage->clean($cutOffDate);

        return $amountDeleted;
    }
}