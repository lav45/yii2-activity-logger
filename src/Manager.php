<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

use lav45\activityLogger\storage\DeleteCommand;
use lav45\activityLogger\storage\MessageData;
use lav45\activityLogger\storage\StorageInterface;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\di\Instance;
use yii\web\IdentityInterface;

class Manager extends BaseObject
{
    public bool $enabled = true;

    public string $user = 'user';

    public string $userNameAttribute = 'username';

    /** @var string|array|StorageInterface */
    public $storage = 'activityLoggerStorage';

    public bool $debug = YII_DEBUG;

    protected function getUserIdentity(): ?IdentityInterface
    {
        /** @var \yii\web\User $user */
        $user = Yii::$app->get($this->user, false);
        if ($user) {
            return $user->getIdentity();
        }
        return null;
    }

    protected function getStorage(): StorageInterface
    {
        if ($this->storage instanceof StorageInterface === false) {
            $this->storage = Instance::ensure($this->storage, StorageInterface::class);
        }
        return $this->storage;
    }

    public function log(MessageData $message): bool
    {
        if (false === $this->enabled) {
            return false;
        }

        if ($identity = $this->getUserIdentity()) {
            $message->userId = $identity->getId();
            $message->userName = $identity->{$this->userNameAttribute};
        }

        try {
            $this->getStorage()->save($message);
            return true;
        } catch (Throwable $e) {
            $this->throwException($e);
            return false;
        }
    }

    public function delete(DeleteCommand $command): bool
    {
        if (false === $this->enabled) {
            return false;
        }
        try {
            $this->getStorage()->delete($command);
            return true;
        } catch (Throwable $e) {
            $this->throwException($e);
            return false;
        }
    }

    private function throwException(Throwable $e): void
    {
        if ($this->debug) {
            throw $e;
        }
        Yii::error($e->getMessage(), static::class);
    }
}