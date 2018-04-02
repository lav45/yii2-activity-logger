<?php
/**
 * Created by PhpStorm.
 * User: and1
 * Date: 01.04.2018
 * Time: 12:33
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
     * @var string
     */
    public $date;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entityName'], 'string'],

            [['date'], 'date', 'format' => 'dd.MM.yyyy'],
        ];
    }

    /**
     * Для красивых ссылок в строке браузера при фильтрации и поиске
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
            $formatter = Yii::$app->formatter;
            $query
                ->andFilterWhere(['and',
                    ['>', 'created_at', $formatter->asTimestamp($this->date . ' 00:00:00 ' . Yii::$app->timeZone)],
                    ['<', 'created_at', $formatter->asTimestamp($this->date . ' 23:59:59 ' . Yii::$app->timeZone)],
                ]);
        }

        $query->andFilterWhere(['entity_name' => $this->entityName]);

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function getEntityNameList()
    {
        $data = array_flip(Yii::$app->getModule('logger')->entityMap);
        return array_combine($data, $data);
    }
}