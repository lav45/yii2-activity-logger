<?php

namespace lav45\activityLogger\contracts;

interface ManagerInterface
{
    /**
     * @param string $entityName
     * @param array $options
     * @return static
     */
    public function createMessage($entityName, array $options);

    /**
     * @return bool
     */
    public function save();

    /**
     * @param string $entityName
     * @param string $entityId
     */
    public function delete($entityName, $entityId);

    /**
     * @return int|bool the number of deleted rows or false if clear range not set
     */
    public function clean();
}