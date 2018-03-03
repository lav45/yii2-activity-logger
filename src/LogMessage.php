<?php

namespace lav45\activityLogger;

use yii\helpers\Json;
use yii\base\BaseObject;

/**
 * Class LogMessage this is a data transfer object
 * @package lav45\activityLogger
 *
 * @property string $entityName alias name target object
 * @property string $entityId id target object
 * @property string $createdAt creation date of the action
 * @property string $userId id user who performed the action
 * @property string $userName user name who performed the action
 * @property string $action the action performed on the object
 * @property string $env environment, which produced the effect
 * @property string $data json data that was modified or relate to the subject
 */
class LogMessage extends BaseObject
{
    /**
     * @var \Closure custom function for the encode `$data`
     */
    public $encode;
    /**
     * @var string
     */
    private $entityName;
    /**
     * @var string
     */
    private $entityId;
    /**
     * @var int
     */
    private $createdAt;
    /**
     * @var string
     */
    private $userId;
    /**
     * @var string
     */
    private $userName;
    /**
     * @var string
     */
    private $action;
    /**
     * @var string
     */
    private $env;
    /**
     * @var mixed
     */
    private $data;

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param string $entityName
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param string $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->createdAt ?: time();
    }

    /**
     * @param int $date
     */
    public function setCreatedAt($date)
    {
        $this->createdAt = $date;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return null|string
     */
    public function getEnv()
    {
        return $this->env;
    }

    /**
     * @param null|string $env
     */
    public function setEnv($env)
    {
        $this->env = $env;
    }

    /**
     * @return string|null
     */
    public function getData()
    {
        if (empty($this->data)) {
            return null;
        }
        return $this->encode($this->data);
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param mixed $data
     * @return string
     */
    private function encode($data)
    {
        if ($this->encode === null) {
            return Json::encode($data);
        }
        return call_user_func($this->encode, $data);
    }
}
