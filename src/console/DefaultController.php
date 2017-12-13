<?php

namespace lav45\activityLogger\console;

use yii\console\Controller;
use lav45\activityLogger\ManagerTrait;

class DefaultController extends Controller
{
    use ManagerTrait;

    /**
     * Clean storage activity log
     */
    public function actionClean()
    {
        $amountDeleted = $this->getLogger()->clean();
        if ($amountDeleted !== false) {
            echo "Deleted {$amountDeleted} record(s) from the activity log.\n";
        }
    }
}