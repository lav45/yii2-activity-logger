<?php

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
        ActivityLogViewModel::setModule($this->module);

        $searchModel = new ActivityLogSearch();
        $searchModel->setEntityMap($this->module->entityMap);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
