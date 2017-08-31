<?php

namespace lav45\activityLogger\contracts;

use yii\base\Configurable;

interface MessageInterface extends Configurable
{
    /**
     * @param string $entityName
     * @param array $config
     */
    public function __construct($entityName, $config = []);

    /**
     * @return string
     */
    public function getEntityName();

    /**
     * @return string
     */
    public function getEntityId();

    /**
     * @param string $entityId
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getCreatedAt();

    /**
     * @return string
     */
    public function getUserId();

    /**
     * @param string $userId
     */
    public function setUserId($userId);

    /**
     * @return string
     */
    public function getUserName();

    /**
     * @param string $userName
     */
    public function setUserName($userName);

    /**
     * @return string
     */
    public function getAction();

    /**
     * @param string $action
     */
    public function setAction($action);

    /**
     * @return string
     */
    public function getData();

    /**
     * @param mixed $data
     */
    public function setData($data);
}