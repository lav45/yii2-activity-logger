<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\modules\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class ActivityLogSearch
 * @package lav45\activityLogger\modules\models
 */
class ActivityLogSearch extends Model
{
    /**
     * @var string
     */
    public $entityName;
    /**
     * @var int|string
     */
    public $entityId;
    /**
     * @var int|string
     */
    public $userId;
    /**
     * @var string
     */
    public $env;
    /**
     * @var string
     */
    public $date;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entityName', 'entityId', 'userId', 'env'], 'string', 'max' => 32],
            [['date'], 'date', 'format' => 'dd.MM.yyyy'],
        ];
    }

    /**
     * For beautiful links in the browser bar when filtering and searching
     * @return string
     */
    public function formName()
    {
        return '';
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ActivityLogViewModel::find()
            ->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if (!empty($this->date)) {
            $time_zone = Yii::$app->getTimeZone();
            $date_from = strtotime("{$this->date} 00:00:00 {$time_zone}");
            $date_to = $date_from + 86399; // + 23:59:59
            $query->andWhere(['between', 'created_at', $date_from, $date_to]);
        }

        $query->andFilterWhere([
            'entity_name' => $this->entityName,
            'entity_id' => $this->entityId,
            'user_id' => $this->userId,
            'env' => $this->env,
        ]);

        return $dataProvider;
    }
}