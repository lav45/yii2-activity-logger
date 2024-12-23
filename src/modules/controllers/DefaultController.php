<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\modules\controllers;

use Yii;
use yii\web\Controller;
use lav45\activityLogger\modules\models\ActivityLogSearch;
use lav45\activityLogger\modules\models\ActivityLogViewModel;

/**
 * Class DefaultController
 * @package lav45\activityLogger\modules\controllers
 *
 * @property \lav45\activityLogger\modules\Module $module
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
