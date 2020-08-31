<?php
/**
 * @link https://github.com/LAV45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

use Exception;
use Throwable;
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
    public $storage = 'activityLoggerStorage';
    /**
     * @var bool
     */
    public $debug = YII_DEBUG;
    /**
     * @var string
     */
    public $env;

    /**
     * @return \yii\web\IdentityInterface|null
     */
    protected function getUserIdentity()
    {
        /** @var \yii\web\User $user */
        $user = Yii::$app->get($this->user, false);
        if ($user) {
            return $user->getIdentity();
        }
        return null;
    }

    /**
     * @return StorageInterface
     */
    protected function getStorage()
    {
        if (!$this->storage instanceof StorageInterface) {
            $this->storage = Instance::ensure($this->storage, StorageInterface::class);
        }
        return $this->storage;
    }

    /**
     * @param LogMessageDTO $message
     * @return bool
     */
    public function log(LogMessageDTO $message)
    {
        if (false === $this->enabled) {
            return false;
        }

        $message->createdAt = time();
        $message->env = $this->env;

        if ($identity = $this->getUserIdentity()) {
            $message->userId = $identity->getId();
            $message->userName = $identity->{$this->userNameAttribute};
        }

        try {
            $this->getStorage()->save($message);
            return true;
        } catch (Exception $e) {
            $this->throwException($e);
        } catch (Throwable $e) {
            $this->throwException($e);
        }
        return false;
    }

    /**
     * @param LogMessageDTO $message
     * @param int|null $old_than
     * @return bool
     */
    public function delete(LogMessageDTO $message, $old_than = null)
    {
        if (false === $this->enabled) {
            return false;
        }

        try {
            $this->getStorage()->delete($message, $old_than);
            return true;
        } catch (Exception $e) {
            $this->throwException($e);
        } catch (Throwable $e) {
            $this->throwException($e);
        }
        return false;
    }

    /**
     * @param Exception|Throwable $e
     * @throws Exception|Throwable
     */
    private function throwException($e)
    {
        if ($this->debug) {
            throw $e;
        }
        Yii::error($e->getMessage(), static::class);
    }
}