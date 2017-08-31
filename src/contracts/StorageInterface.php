<?php

namespace lav45\activityLogger\contracts;

interface StorageInterface
{
    /**
     * @param MessageInterface $message
     * @return int
     */
    public function save(MessageInterface $message);

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