<?php
/**
 * Created by PhpStorm.
 * User: lav45
 * Date: 27.11.17
 * Time: 0:22
 */

namespace lav45\activityLogger;

use yii\di\Instance;
use lav45\activityLogger\contracts\ManagerInterface;

trait ManagerTrait
{
    /**
     * @var ManagerInterface|string|array
     */
    private $logger = 'activityLogger';

    /**
     * @return ManagerInterface
     */
    public function getLogger()
    {
        if (!$this->logger instanceof ManagerInterface) {
            $this->logger = Instance::ensure($this->logger, ManagerInterface::class);
        }
        return $this->logger;
    }

    /**
     * @param $data ManagerInterface|string|array
     */
    public function setLogger($data)
    {
        $this->logger = $data;
    }
}