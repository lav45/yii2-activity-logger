# yii2-activity-logger

Tools to store user activity log for Yii2


## Install

```bash
~$ composer require --prefer-dist lav45/yii2-activity-logger
~$ yii migrate --migration-path=vendor/lav45/yii2-activity-logger/migrates
```


## Settings

```php
return [
    'modules' => [
        'logger' => [
            'class' => 'lav45\activityLogger\modules\Module',
            'createUserUrl' => function($id) {
                  return \yii\helpers\Url::toRoute(['/user/default/view', 'id' => $id]);
            },
            'entityMap' => [
                'news' => 'common\models\News',
            ]
        ]
    ],
    'components' => [
        'activityLogger' => [
            'class' => 'lav45\activityLogger\Manager',
        ]
    ]
];
```


### Create link to activity logger module

```php
// This page display activity logs for all entity "news"
Url::toRoute(['/logger/default/index', 'entityName' => 'news']);

// This page display activity logs for entity "news" and "id" => 1
Url::toRoute(['/logger/default/index', 'entityName' => 'news', 'entityId' => 1]);
```


## Example usage to ActiveRecord model

```php
/**
 * @mixin \lav45\activityLogger\ActiveRecordBehavior
 */
class News extends ActiveRecord
{
    // Recommended
    public function rules()
    {
        return [
            // If a field value is not required use `default` validator.
            // If a field is not filled, it will set its value to NULL.

            [['parent_id'], 'integer'],
            [['parent_id'], 'default'],

            [['comment'], 'string'],
            [['comment'], 'default'],
        ];
    }

    // Recommended
    public function transactions()
    {
        return [
            ActiveRecord::SCENARIO_DEFAULT => ActiveRecord::OP_ALL,
        ];
    }

    public function behaviors()
    {
        return [
            ['class' => 'yii\behaviors\AttributeTypecastBehavior'], // Recommended
            [
                'class' => 'lav45\activityLogger\ActiveRecordBehavior',
                'attributes' => [
                    'name',
                    'status' => [
                        'list' => 'statusList'
                    ],
                    'template_id' => [
                        'relation' => 'template',
                        'attribute' => 'name'
                    ],
                ]
            ]
        ];
    }
}
```


## Manual usage

```php
    /**
     * @param string $entityName
     * @param string $messageText
     * @param null|string $action
     * @param null|string $entityId
     * @return bool
     */
    public function log($entityName, $messageText, $action = null, $entityId = null);

    Yii::$app->activityLogger->log($model->getEntityName(), 'export data');
    Yii::$app->activityLogger->log($model->getEntityName(), 'export data', 'download');
    Yii::$app->activityLogger->log($model->getEntityName(), 'export data', 'send mail', $model->getEntityId());
```
