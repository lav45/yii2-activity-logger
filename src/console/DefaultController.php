<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\console;

use lav45\activityLogger\LogMessageDTO;
use lav45\activityLogger\ManagerTrait;
use yii\console\Controller;
use yii\helpers\Console;

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
     * Valid values:
     * 1h - 1 hour
     * 2d - 2 days
     * 3m - 3 month
     * 1y - 1 year
     */
    public $oldThan = '1y';

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
            'oldThan',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'o' => 'old-than',
            'a' => 'log-action',
            'eid' => 'entity-id',
            'e' => 'entity-name',
            'uid' => 'user-id',
        ]);
    }

    /**
     * Clean storage activity log
     */
    public function actionClean()
    {
        $old_than = $this->parseDate($this->oldThan);
        if (null === $old_than) {
            $this->stderr("Invalid date format\n", Console::FG_RED, Console::UNDERLINE);
            return;
        }

        $message = new LogMessageDTO([
            'entityName' => $this->entityName,
            'entityId' => $this->entityId,
            'userId' => $this->userId,
            'action' => $this->logAction,
            'env' => $this->env,
        ]);

        if ($this->getLogger()->delete($message, $old_than)) {
            $this->stdout("Successful clearing the logs.\n");
        } else {
            $this->stdout("Error while cleaning the logs.\n");
        }
    }

    /**
     * @param string $str
     * @return int|null
     */
    private function parseDate($str)
    {
        if (preg_match("/^(\d+)([dmy]{1})$/", $str, $matches)) {
            $count = $matches[1];
            $alias = $matches[2];
            $aliases = [
                'd' => 'day',
                'm' => 'month',
                'y' => 'year',
            ];
            if (isset($aliases[$alias])) {
                return strtotime("-{$count} {$aliases[$alias]} 0:00 UTC");
            }
        }
        return null;
    }
}
