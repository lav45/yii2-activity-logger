<?php

namespace lav45\activityLogger;

use yii\di\Instance;

trait ManagerTrait
{
    /**
     * @var Manager|string|array
     */
    private $logger = 'activityLogger';

    /**
     * @return Manager
     */
    public function getLogger()
    {
        if (!$this->logger instanceof Manager) {
            $this->logger = Instance::ensure($this->logger, Manager::class);
        }
        return $this->logger;
    }

    /**
     * @param $data Manager|string|array
     */
    public function setLogger($data)
    {
        $this->logger = $data;
    }
}
