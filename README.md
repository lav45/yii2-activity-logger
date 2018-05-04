# yii2-activity-logger

<table>
    <tr>
        <td width="200">
            <img src="https://user-images.githubusercontent.com/675367/33967884-6dc55ca8-e076-11e7-88c5-4ba5d7d69012.png" alt="yii2-activity-logger" />
        </td>
        <td>
            Это расширение поможет вам отслеживать пользовательскую активность на сайте.
            Когда в админке над контентом работает больше двух человек, не всегда понятно кто, когда и зачем сделал изменения в описание статьи, убрал статью из публикации, добавил непонятного пользователя, удалил организацию.
            Для того чтобы была возможность поблагодарить автора за усердную работу и был разработан этот модуль.
        </td>
    </tr>
</table>


[![Latest Stable Version](https://poser.pugx.org/lav45/yii2-activity-logger/v/stable)](https://packagist.org/packages/lav45/yii2-activity-logger)
[![License](https://poser.pugx.org/lav45/yii2-activity-logger/license)](https://github.com/lav45/yii2-activity-logger/blob/master/LICENSE.md)
[![Total Downloads](https://poser.pugx.org/lav45/yii2-activity-logger/downloads)](https://packagist.org/packages/lav45/yii2-activity-logger)


## Установка расширения

```bash
~$ composer require --prefer-dist lav45/yii2-activity-logger
```


## Миграции

Для начала нужно настроить `MigrateController`, таким образом чтобы он получал миграции из нескольких источников.
В настройках консольного окружения необходимо добавить следующий код:

```php
return [
    'controllerMap' => [
        'migrate' => [
            'class' => \yii\console\controllers\MigrateController::class,
            'migrationPath' => [
                '@app/migrations',
                '@vendor/lav45/yii2-activity-logger/migrations',
            ],
        ],
    ],
];
```

Запускаем миграции

```bash
~$ yii migrate
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
            'class' => \lav45\activityLogger\modules\Module::class,

            // Список моделей которые логировались
            'entityMap' => [
                'news' => 'common\models\News',
            ],
        ]
    ],
    'components' => [
        /**
         * Компонент принимает и управляет логами
         */
        'activityLogger' => [
            'class' => \lav45\activityLogger\Manager::class,

            // Включаем логирование для PROD версии
            'enabled' => YII_ENV_PROD,

            // при вызове метода `clean()` будут удалены все данные добавленные 365 дней назад
            'deleteOldThanDays' => 365,

            // идентификатор компонента `\yii\web\User`
            'user' => 'user',

            // Поле для отображения имени из модели пользователя
            'userNameAttribute' => 'username',

            // идентификатор компонента хранилища логов `\lav45\activityLogger\StorageInterface`
            'storage' => 'activityLoggerStorage',

            'messageClass' => [
                'class' => \lav45\activityLogger\LogMessage::class,

                // При использовании компанета когда пользователь ещё не авторизировался его действия
                // можно записывать от имени "Неизвесный пользователь", к примеру.
                'userId' => 'cron',
                'userName' => 'Неизвесный пользователь',

                // Окружение из которого проиводило действие
                'env' => 'console',

                // Так же можно указать значение по умолчанию и для других параметров
                // 'entityId' => '...',
                // 'createdAt' => time(),
                // 'action' => '...',
                // 'data' => [" ... "],
            ],
        ],

        /**
         * Компонент принимает и управляет логами
         */
        'activityLoggerStorage' => [
            'class' => \lav45\activityLogger\DbStorage::class,

            // Имя таблицы в которой будут хранится логи
            'tableName' => '{{%activity_log}}',

            // идентификатор компонента `\yii\db\Connection`
            'db' => 'db',
        ],
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
 * @mixin \lav45\activityLogger\ActiveLogBehavior
 */
class News extends ActiveRecord
{
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
            [
                'class' => \lav45\activityLogger\ActiveLogBehavior::class,
             
                // Если необхадимо изменить стандартное значение `entityName`
                'getEntityName' => function () {
                    return 'global_news';
                },
                // Если необхадимо изменить стандартное значение `entityId`
                'getEntityId' => function () {
                    return $this->global_news_id;
                }

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

    /**
     * Если необхадимо форматировать отобоажемое значение
     * Можно указать любой поддерживаемый формат компонентом `\yii\i18n\Formatter`
     * или использовать произвольную функцию
     * @return array
     */
    public function attributeFormats()
    {
        return [
            // Значение аттрибуда будет форматироваться с помощью Yii::$app->formatter->asDatetime($value);
            'published_at' => 'datetime',

            // Можно использовать свою функцию обратного вызова 
            'is_published' => function($value) {
                return Yii::$app->formatter->asBoolean($value);
            },

            // Если нужно вывести имени картинки и ссылку на неё
            'image' => function($value) {
                if (empty($value)) { return null; }

                $url = "https://cdn.site.com/img/{$value}";
                return Html::a($value, $url, ['target' => '_blank']);
            }
        ];
    }
    
    /**
     * В процессе работы `\lav45\activityLogger\ActiveLogBehavior` вызывает событие
     * [[ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE]] - перед записью логов
     * [[ActiveLogBehavior::EVENT_AFTER_SAVE_MESSAGE]] - после записи логов
     */
    public function init()
    {
        parent::init();
        
        // Регистрируем обработчики событий
        $this->on(ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE, 
            function (\lav45\activityLogger\MessageEvent $event) {
                // Вы можете добавить в список логов свою информацию
                $event->append[] = 'Reset password';
            });
        
        $this->on(ActiveLogBehavior::EVENT_AFTER_SAVE_MESSAGE, 
            function (\yii\base\Event $event) {
                // Какие-то действия после записи логов
            });
    }
    
    /*
     * В место регистрации события вы можете создать однаименный метод который будет вызываться вместо события
     */

    /**
     * Будет вызываться в место события [[ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE]]
     * @return array
     */
    public function beforeSaveMessage()
    {
        // Вы можете добавить в список логов свою информацию
        return [
            'Reset password',
            // или заменить отображаемое значение в логах для атрибута `password_hash`
            'password_hash' => 'Reset password',
        ];
    }

    /**
     * Будет вызываться в место события [[ActiveLogBehavior::EVENT_AFTER_SAVE_MESSAGE]]
     */
    public function afterSaveMessage()
    {
        // Какие-то действия после записи логов
    }
}
```


## Добавим консольный контроллер для очистки логов

Это не обязательное расширение. Если вы не планируете удалять устаревшие логи, можете пропустить этот пункт.

```php
return [
    'controllerMap' => [
        'logger' => [
            'class' => \lav45\activityLogger\console\DefaultController::class
        ]
    ],
];
```

Теперь можно периодически чистить устаревшие логи выполняя команду из консоли

```bash
~$ yii logger/clean
# => Deleted 5 record(s) from the activity log.
```

### Используя параметры командной строки

* `--entity-id, -eid`: string. Идентификатор целевого объекта

* `--entity-name, -e`: string. Псевдоним имени целевого объекта

* `--user-id, -uid`: string. Идентификатор пользователя, который выполнил действие

* `--log-action, -a`: string. Действие, которое было произведено над объектом

* `--env`: string. Среда, из которой производилось действие

* `--old-than, -o`: string. Для удаления данные старше N
Допустивые значения: 1h - старше 1 часа, 2d - старше 2 дней, 3m - старше 3-х месяцев, 4y - старше 4 лет.
При этом параметр deleteOldThanDays будет проигнорирован.   


В следующем примере показано, как можно использовать эти параметры.

Например если вы хотите удалить старые запись из логов для консольного окружения, для этого вы можете использовать следующую команду:

```
~$ yii logger/clean --env=console --old-than=30d
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
    $message = 'export data';

    $logger = Yii::$app->activityLogger;

    // Сохранение текстового сообщения слязанного с $entityName
    $logger->log($entityName, $message);

    // Сохранение текстового сообщения слязанного с $entityName при выполнении действия "download"
    $logger->log($entityName, $message, 'download');

    // Сохранение текстового сообщения слязанного с $entityName и $entityId при выполнении действия "send mail"
    $logger->log($entityName, $message, 'send mail', $entityId);
```


Когда в логах нужно оставить одну запись со списком выполненных действий можно воспользоваться `LogCollection`
В данном примере мы записываем лог синхронизации пользователей

```php
$collection = Yii::$app->activityLogger->createCollection('user');
$collection->setAction('sync');
$collection->setEntityId(100);

$messages = [
    'Created: 100',
    'Updated: 100500',
    'Deleted: 5',
];

/**
 * Добавляем все необходимые записи
 */
foreach ($messages as $message) {
    $collection->addMessage($message);
}

/**
 * Сохраняем все собранные на данный момент логи
 * Посли записи список логов будет очищен
 */
$collection->push(); // => true
```


### Удаление устаревших данных

Будут удалены все логи старше одного года. Этот параметр можно изменить в настройках компонента, указав свое значение для параметра `deleteOldThanDays`

```php
Yii::$app->activityLogger->clean();
```


# Лицензии

Для получения информации о лицензии проверьте файл [LICENSE.md](LICENSE.md).
