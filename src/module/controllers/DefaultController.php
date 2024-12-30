<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\module\controllers;

use Yii;
use yii\web\Controller;
use lav45\activityLogger\module\models\ActivityLogSearch;
use lav45\activityLogger\module\models\ActivityLogViewModel;

/**
 * @property \lav45\activityLogger\module\Module $module
 */
class DefaultController extends Controller
{
    public function actionIndex()
    {
        Yii::$container->set(ActivityLogViewModel::class, [
            'entityMap' => $this->module->entityMap
        ]);

        $searchModel = new ActivityLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->getRequest()->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
