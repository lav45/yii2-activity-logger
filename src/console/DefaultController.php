<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\console;

use lav45\activityLogger\ManagerInterface;
use lav45\activityLogger\storage\DeleteCommand;
use yii\base\Module;
use yii\console\Controller;

class DefaultController extends Controller
{
    /** Target entity name */
    public ?string $entityName = null;
    /** Entity target id */
    public ?string $entityId = null;
    /** User id who performed the action */
    public ?string $userId = null;
    /** Action performed on the object */
    public ?string $logAction = null;
    /** Environment, which produced the effect */
    public ?string $env = null;
    /**
     * Delete older than
     * Valid values:
     * 1h - 1 hour
     * 2d - 2 days
     * 3m - 3 month
     * 1y - 1 year
     */
    public ?string $oldThan = null;

    private ManagerInterface $logger;

    public function __construct(
        string           $id,
        Module           $module,
        ManagerInterface $logger,
        array            $config = []
    )
    {
        parent::__construct($id, $module, $config);
        $this->logger = $logger;
    }

    public function options($actionID): array
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

    public function optionAliases(): array
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
    public function actionClean(): void
    {
        $oldThan = $this->parseDate($this->oldThan);

        $command = new DeleteCommand([
            'entityName' => $this->entityName,
            'entityId' => $this->entityId,
            'userId' => $this->userId,
            'action' => $this->logAction,
            'env' => $this->env,
            'oldThan' => $oldThan,
        ]);

        if ($this->logger->delete($command)) {
            $this->stdout("Successful clearing the logs.\n");
        } else {
            $this->stdout("Error while cleaning the logs.\n");
        }
    }

    private function parseDate(?string $str): ?int
    {
        if (empty($str)) {
            return null;
        }
        if (preg_match("/^(\d+)([hdmy]+)$/", $str, $matches)) {
            [$_, $count, $alias] = $matches;
            $aliases = [
                'h' => 'hour',
                'd' => 'day',
                'm' => 'month',
                'y' => 'year',
            ];
            return strtotime("-{$count} {$aliases[$alias]} 0:00:00 UTC");
        }
        throw new \InvalidArgumentException("Invalid old-than value: '{$str}'. You can use one of the 1h, 2d, 3m or 4y");
    }
}
