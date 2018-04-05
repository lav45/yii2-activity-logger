<?php

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
     * @var array
     */
    private $entityMap;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entityName'], 'in', 'range' => array_keys($this->getEntityMap())],

            [['entityId'], 'safe'],

            [['userId'], 'safe'],

            [['env'], 'safe'],

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

        if (!($this->load($params, '') && $this->validate())) {
            return $dataProvider;
        }

        if (!empty($this->date)) {
            $date = Yii::$app->getFormatter()
                ->asTimestamp($this->date . ' 00:00:00 ' . Yii::$app->timeZone);

            $query
                ->andFilterWhere(['and',
                    ['>=', 'created_at', $date],
                    ['<=', 'created_at', $date + 86400],
                ]);
        }

        $query
            ->andFilterWhere(['entity_name' => $this->entityName])
            ->andFilterWhere(['entity_id' => $this->entityId])
            ->andFilterWhere(['user_id' => $this->userId])
            ->andFilterWhere(['env' => $this->env]);

        return $dataProvider;
    }

    /**
     * @return array
     */
    protected function getEntityMap()
    {
        return $this->entityMap;
    }

    /**
     * @param array $value
     */
    public function setEntityMap($value)
    {
        $this->entityMap = $value;
    }

    /**
     * @return array
     */
    public function getEntityNameList()
    {
        $data = array_keys($this->getEntityMap());
        return array_combine($data, $data);
    }
}