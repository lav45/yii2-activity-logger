<?php

namespace lav45\activityLogger;

use yii\helpers\Json;
use yii\base\BaseObject;

/**
 * Class LogMessage
 * @package lav45\activityLogger
 *
 * @property string $entityId id target object
 * @property string $createdAt creation date of the action
 * @property string $userId id user who performed the action
 * @property string $userName user name who performed the action
 * @property string $action action which was made with the object
 * @property string $env environment from which produces action
 * @property string $data json data that was modified or relate to the subject
 */
class LogMessage extends BaseObject
{
    /**
     * @var \Closure
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
     * @var string|null
     */
    private $userId;
    /**
     * @var string|null
     */
    private $userName;
    /**
     * @var string|null
     */
    private $action;
    /**
     * @var string|null
     */
    private $env;

    /**
     * @var mixed|null
     */
    private $data;

    /**
     * LogMessage constructor.
     * @param string $entityName
     * @param array $config
     */
    public function __construct($entityName, $config = [])
    {
        $this->entityName = $entityName;
        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
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
     * @param $data
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