<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\module\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;

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
     */
    public function search(): DataProviderInterface
    {
        $query = ActivityLog::find()
            ->orderBy(['id' => SORT_DESC]);

        if (!empty($this->date)) {
            $timeZone = Yii::$app->getFormatter()->timeZone;
            $dateFrom = strtotime("{$this->date} 00:00:00 {$timeZone}");
            $dateTo = $dateFrom + 86399; // + 23:59:59
            $query->andWhere(['between', 'created_at', $dateFrom, $dateTo]);
        }

        $query->andFilterWhere([
            'entity_name' => $this->entityName,
            'entity_id' => $this->entityId,
            'user_id' => $this->userId,
            'env' => $this->env,
        ]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
        ]);
    }
}