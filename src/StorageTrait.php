<?php

namespace lav45\activityLogger;

use yii\di\Instance;

/**
 * Trait StorageTrait
 * @package lav45\activityLogger
 */
trait StorageTrait
{
    /**
     * @var string|StorageInterface
     */
    private $storage = 'activityLoggerStorage';

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        if (!$this->storage instanceof StorageInterface) {
            $this->storage = Instance::ensure($this->storage, StorageInterface::class);
        }
        return $this->storage;
    }

    /**
     * @param string $storage
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
    }
}