<?php

namespace lav45\activityLogger\console;

use yii\console\Controller;
use lav45\activityLogger\ManagerTrait;

/**
 * Class DefaultController
 * @package lav45\activityLogger\console
 */
class DefaultController extends Controller
{
    use ManagerTrait;

    /**
     * @var string alias name target object
     */
    public $entityName;
    /**
     * @var string id target object
     */
    public $entityId;
    /**
     * @var string id user who performed the action
     */
    public $userId;
    /**
     * @var string the action performed on the object
     */
    public $logAction;
    /**
     * @var string environment, which produced the effect
     */
    public $env;
    /**
     * @var string delete old than days
     */
    public $deleteOldThanDays;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(parent::options($actionID), [
            'entityName',
            'entityId',
            'userId',
            'logAction',
            'env',
            'deleteOldThanDays',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'd' => 'delete-old-than-days',
            'a' => 'log-action',
            'eid' => 'entity-id',
            'n' => 'entity-name',
            'uid' => 'user-id',
            'e' => 'env',
        ]);
    }

    /**
     * Clean storage activity log
     */
    public function actionClean()
    {
        $options = array_filter([
            'entityName' => $this->entityName,
            'entityId' => $this->entityId,
            'userId' => $this->userId,
            'action' => $this->logAction,
            'env' => $this->env,
        ]);

        if (isset($this->deleteOldThanDays)) {
            switch ($this->deleteOldThanDays) {
                case '1h':
                    $deleteOldThanDays = 0.041666666666667;
                    break;
                case '2d':
                    $deleteOldThanDays = 2;
                    break;
                case '3m':
                    $deleteOldThanDays = 90;
                    break;
                case '1y':
                    $deleteOldThanDays = 365;
                    break;
                default:
                    $deleteOldThanDays = (int) $this->deleteOldThanDays;
                    break;
            }

            $options = array_merge($options, ['deleteOldThanDays' => $deleteOldThanDays]);
        }

        $amountDeleted = $this->getLogger()->clean($options);

        if ($amountDeleted !== false) {
            echo "Deleted {$amountDeleted} record(s) from the activity log.\n";
        }
    }
}
