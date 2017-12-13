<?php

namespace lav45\activityLogger;

interface StorageInterface
{
    /**
     * @param LogMessage $message
     * @return int
     */
    public function save($message);

    /**
     * @param int $date
     * @return int
     */
    public function clean($date);

    /**
     * @param string $entityName
     * @param string|null $entityId
     * @return int
     */
    public function delete($entityName, $entityId);
}