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
use yii\web\IdentityInterface;

class Manager extends BaseObject implements ManagerInterface
{
    public string $user = 'user';

    public string $userNameAttribute = 'username';

    public bool $debug = YII_DEBUG;

    private bool $enabled = true;

    private StorageInterface $storage;

    public function __construct(
        StorageInterface $storage,
        array            $config = []
    )
    {
        parent::__construct($config);
        $this->storage = $storage;
    }

    protected function getUserIdentity(): ?IdentityInterface
    {
        /** @var \yii\web\User $user */
        $user = Yii::$app->get($this->user, false);
        if ($user) {
            return $user->getIdentity();
        }
        return null;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function log(MessageData $message): bool
    {
        if (false === $this->isEnabled()) {
            return false;
        }

        if ($identity = $this->getUserIdentity()) {
            $message->userId = $identity->getId();
            $message->userName = $identity->{$this->userNameAttribute};
        }

        try {
            $this->storage->save($message);
            return true;
        } catch (Throwable $e) {
            $this->throwException($e);
            return false;
        }
    }

    public function delete(DeleteCommand $command): bool
    {
        if (false === $this->isEnabled()) {
            return false;
        }
        try {
            $this->storage->delete($command);
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