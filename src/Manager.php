<?php

namespace lav45\activityLogger;

use Yii;
use yii\base\BaseObject;
use yii\di\Instance;

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
     * @var array
     */
    public $messageClass = [
        'class' => LogMessage::class
    ];
    /**
     * @var string|StorageInterface
     */
    public $storage = 'activityLoggerStorage';
    /**
     * @var bool
     */
    public $debug = YII_DEBUG;

    /**
     * @return array
     */
    protected function getUserOptions()
    {
        if ($user = Yii::$app->get($this->user, false)) {
            /** @var \yii\web\IdentityInterface $identity */
            $identity = $user->identity;
            return [
                'userId' => $identity->getId(),
                'userName' => $identity->{$this->userNameAttribute}
            ];
        }
        return [];
    }

    /**
     * @return StorageInterface
     */
    private function getStorage()
    {
        if (!$this->storage instanceof StorageInterface) {
            $this->storage = Instance::ensure($this->storage, StorageInterface::class);
        }
        return $this->storage;
    }

    /**
     * @param string $entityName
     * @return LogCollection
     * @since 1.5.1
     */
    public function createCollection($entityName)
    {
        return Yii::createObject(['class' => LogCollection::class], [$this, $entityName]);
    }

    /**
     * @param string $entityName
     * @param string|array $message
     * @param null|string $action
     * @param null|string|int $entityId
     * @return bool
     */
    public function log($entityName, $message, $action = null, $entityId = null)
    {
        if (empty($entityName) || empty($message)) {
            return false;
        }
        if (is_string($message)) {
            $message = [$message];
        }
        return $this->saveMessage([
            'entityName' => $entityName,
            'entityId' => $entityId,
            'data' => $message,
            'action' => $action,
        ]);
    }

    /**
     * @param array $options
     *  - entityName :string
     *  - entityId :string|int
     *  - createdAt :int unix timestamp
     *  - userId :string
     *  - userName :string
     *  - action :string
     *  - env :string
     *  - data :array
     *
     * @return bool
     */
    private function saveMessage(array $options)
    {
        if ($this->enabled === false) {
            return false;
        }
        $options = array_filter($options);
        if (empty($options)) {
            return false;
        }

        /** @var LogMessage $message */
        $message = Yii::createObject(array_merge(
            $this->messageClass,
            $this->getUserOptions(),
            ['createdAt' => time()],
            $options
        ));

        try {
            $result = $this->getStorage()->save($message);
        } catch (\Exception $e) {
            return $this->throwException($e);
        } catch (\Throwable $e) {
            return $this->throwException($e);
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
        return $this->deleteMessage([
            'entityName' => $entityName,
            'entityId' => $entityId,
        ]);
    }

    /**
     * @param array $options
     *  - entityName :string
     *  - entityId :string|int
     *  - userId :string
     *  - action :string
     *  - env :string
     *
     * @return int|bool the count of deleted rows or false if clear range not set
     */
    public function clean(array $options = [])
    {
        if ($this->deleteOldThanDays === false) {
            return false;
        }

        $options['createdAt'] = time() - $this->deleteOldThanDays * 86400;

        return $this->deleteMessage($options);
    }

    /**
     * @param array $options
     *  - entityName :string
     *  - entityId :string|int
     *  - createdAt :int unix timestamp
     *  - userId :string
     *  - action :string
     *  - env :string
     *
     * @return int|bool the count of deleted rows or false if clear range not set
     */
    private function deleteMessage(array $options)
    {
        $options['class'] = $this->messageClass['class'];
        /** @var LogMessage $message */
        $message = Yii::createObject($options);

        try {
            return $this->getStorage()->delete($message);
        } catch (\Exception $e) {
            return $this->throwException($e);
        } catch (\Throwable $e) {
            return $this->throwException($e);
        }
    }

    /**
     * @param \Exception|\Throwable $e
     * @throws \Exception|\Throwable
     * @return bool
     */
    private function throwException($e)
    {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        if ($this->debug) {
            throw $e;
        }
        Yii::warning($e->getMessage());
        return false;
    }
}
