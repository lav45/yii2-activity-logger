# yii2-activity-logger

<table>
    <tr>
        <td width="200">
            <img src="https://user-images.githubusercontent.com/675367/33967884-6dc55ca8-e076-11e7-88c5-4ba5d7d69012.png" alt="yii2-activity-logger" />
        </td>
        <td>
            Эта утилита поможет вам отслеживать пользовательской активности на сайте.
            Когда в админке над контентом работает больше двух человек, не всегда понятно кто и зачем сделал изменения в посте, убрал статью из публикации, добавил непонятного пользователя, удалил организацию.
            Для того чтобы была возможность поблагодарить автора за усердную работу и был разработан этот модуль.
        </td>
    </tr>
</table>


## Установка

```bash
~$ composer require --prefer-dist lav45/yii2-activity-logger
~$ yii migrate --migration-path=vendor/lav45/yii2-activity-logger/migrations
```


## Подключение
Необходимо добавить в конфигурационный файл

```php
return [
    'modules' => [
        /**
         * Модуль будет использоваться для просмотра логов
         */
        'logger' => [
            'class' => 'lav45\activityLogger\modules\Module',
            'entityMap' => [
                'news' => 'common\models\News',
            ]
        ]
    ],
    'components' => [
        /**
         * Компонент принимает и управляет логами
         */
        'activityLogger' => [
            'class' => 'lav45\activityLogger\Manager',

// ================ Значения используемые по умолчанию ================

            // Включаем логирование для PROD версии
            'enabled' => YII_ENV_PROD,

            // при вызове метода `clean()` будут удалены все данные добавленные 365 дней назад
            'deleteOldThanDays' => 365,

            // string|null идентификатор компонента `\yii\web\User`
            'user' => 'user',

            // Поле для отображения имени из модели пользователя
            'userNameAttribute' => 'username',

            // Настройки для хранилища
            'storage' => [
                'class' => 'lav45\activityLogger\DbStorage',
                'tableName' => '{{%activity_log}}',
                'db' => 'db',
            ],
        ]
    ]
];
```


### Создаем ссылки для просмотра записанных логов

```php
// На этой странице можно просмотреть все логи
Url::toRoute(['/logger/default/index']);

// На этой странице можно просмотреть журналы действий конкретного пользователя по го `$id`
Url::toRoute(['/logger/default/index', 'userId' => 1]);

// На этой странице можно просмотреть журналы действий для всех объектов "news"
Url::toRoute(['/logger/default/index', 'entityName' => 'news']);

// На этой странице можно просмотреть журналы действий для всех объектов "news" с "id" => 1
Url::toRoute(['/logger/default/index', 'entityName' => 'news', 'entityId' => 1]);
```


## Пример использования для ActiveRecord модели

```php
/**
 * @mixin \lav45\activityLogger\ActiveRecordBehavior
 */
class News extends ActiveRecord
{
    // Рекомендуется использовать
    public function rules()
    {
        return [
            // Если значение поля не обязательное, тогда используйте валидатор `default`
            // тогда если поле не будет заполнено, ему будет присвоено значение NULL.

            [['parent_id'], 'integer'],
            [['parent_id'], 'default'],

            [['comment'], 'string'],
            [['comment'], 'default'],
        ];
    }

    // Рекомендуется использовать
    public function transactions()
    {
        return [
            ActiveRecord::SCENARIO_DEFAULT => ActiveRecord::OP_ALL,
        ];
    }

    public function behaviors()
    {
        return [
            // Рекомендуется использовать для корректной работы поиска измененных полей
            ['class' => 'yii\behaviors\AttributeTypecastBehavior'],
            [
                'class' => 'lav45\activityLogger\ActiveRecordBehavior',
                // Список полей за изменением которых будет производиться слежение
                'attributes' => [
                    // Простые поля ( string|int|bool )
                    'name',

                    // Поля значение которого можно найти в списке.
                    // в данном случае `$model->getStatusList()[$model->status]`
                    'status' => [
                        'list' => 'statusList',
                    ],

                    // Поле значение которого является `id` связи с другой моделью
                    'template_id' => [
                        'relation' => 'template',
                        // Поле из связанной таблицы которое будет использовано в качестве отображаемого значения
                        'attribute' => 'name',
                    ],
                ]
            ]
        ];
    }
}
```


## Добавим консольный контроллер для очистки логов

Это не обязательное расширение. Если вы не планируете удалять устаревшие логи, можете пропустить этот пункт.

```php
return [
    'controllerMap' => [
        'logger' => [
            'class' => 'lav45\activityLogger\console\DefaultController'
        ]
    ],
];
```

Теперь можно периодически чистить устаревшие логи выполняя команду из консоли

```bash
~$ yii logger/clean
Deleted 5 record(s) from the activity log.
```


## Ручное использование компонента

### Добавление логов

Пригодится в тех случаях когда в процессе работы приложения не используются ActiveRecord модели.
Например при отправке отчетов, скачивании файлов, работа с внешним API, и т.д

```php
    // имя сущности
    $entityName = 'user';
    // id сущности с которой производится действие
    $entityId = 10;
    // текст с описанием действия
    $messageText = 'export data';

    $logger = Yii::$app->activityLogger;

    $logger->log($entityName, $messageText);

    $logger->log($entityName, $messageText, 'download');

    $logger->log($entityName, $messageText, 'send mail', $entityId);
    // или можно использовать полнофункциональный вариант
    $logger
        ->createMessage($entityName, [
            'entityId' => $entityId,
            'data' => [$messageText],
            'action' => 'send mail',
        ])
        ->save();
```

### Удаление устаревших данных

Будут удалены все логи старше одного года. Этот параметр можно изменить в настройках компонента, указав свое значение для параметра `deleteOldThanDays`

```php
Yii::$app->activityLogger->clean();
```