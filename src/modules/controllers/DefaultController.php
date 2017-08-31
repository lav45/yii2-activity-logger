<?php

namespace lav45\activityLogger\modules\controllers;

use yii\web\Controller;
use yii\data\ActiveDataProvider;
use lav45\activityLogger\modules\models\ActivityLogViewModel;

/**
 * Class DefaultController
 * @package lav45\activityLogger\modules\controllers
 *
 * @property \lav45\activityLogger\modules\Module $module
 */
class DefaultController extends Controller
{
    public function actionIndex($entityName, $entityId = null)
    {
        ActivityLogViewModel::setModule($this->module);

        $query = ActivityLogViewModel::find()
            ->where(['entity_name' => $entityName])
            ->andFilterWhere(['entity_id' => $entityId])
            ->orderBy(['created_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
}