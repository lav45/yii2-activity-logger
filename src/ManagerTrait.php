<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

use yii\di\Instance;

/**
 * Trait ManagerTrait
 * @package lav45\activityLogger
 */
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
