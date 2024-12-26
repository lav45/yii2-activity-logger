# yii2-activity-logger

<table>
    <tr>
        <td>
            <img width="200px" src="https://user-images.githubusercontent.com/675367/33967884-6dc55ca8-e076-11e7-88c5-4ba5d7d69012.png" alt="yii2-activity-logger" />
        </td>
        <td>
            Это расширение поможет вам отслеживать пользовательскую активность на сайте.
            Когда в админке над контентом работает больше двух человек, не всегда понятно кто, когда и зачем сделал изменения в описание статьи, убрал статью из публикации, добавил непонятного пользователя, удалил организацию.
            Для того чтобы была возможность поблагодарить автора за усердную работу и был разработан этот модуль.
        </td>
    </tr>
</table>


[![Latest Stable Version](https://poser.pugx.org/lav45/yii2-activity-logger/v/stable?cache=clear)](https://packagist.org/packages/lav45/yii2-activity-logger)
[![License](https://poser.pugx.org/lav45/yii2-activity-logger/license)](https://github.com/lav45/yii2-activity-logger/blob/master/LICENSE.md)
[![Total Downloads](https://poser.pugx.org/lav45/yii2-activity-logger/downloads)](https://packagist.org/packages/lav45/yii2-activity-logger)
[![Test Status](https://github.com/lav45/yii2-activity-logger/workflows/test/badge.svg)](https://github.com/lav45/yii2-activity-logger/actions)
[![Code Coverage](https://scrutinizer-ci.com/g/lav45/yii2-activity-logger/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lav45/yii2-activity-logger/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lav45/yii2-activity-logger/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lav45/yii2-activity-logger/)

## Установка расширения

```bash
composer require --prefer-dist lav45/yii2-activity-logger
```

## Миграции

Для начала нужно настроить `MigrateController` таким образом, чтобы он получал миграции из нескольких источников.
В настройках консольного окружения необходимо добавить следующий код:

```php
return [
    'controllerMap' => [
        'migrate' => [
            '__class' => \yii\console\controllers\MigrateController::class,
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
yii migrate
```

## Подключение

Необходимо добавить в конфигурационный файл

```php
Yii::$container->setDefinitions([
    \lav45\activityLogger\ManagerInterface::class => static fn() => Instance::ensure('activityLogger'),
    \lav45\activityLogger\storage\StorageInterface::class => static fn() => Instance::ensure('activityLoggerStorage'),
]);

return [
    'modules' => [
        /**
         * Модуль будет использоваться для просмотра логов
         */
        'logger' => [
            '__class' => \lav45\activityLogger\modules\Module::class,
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
            '__class' => \lav45\activityLogger\Manager::class,
            // Включаем логирование только для PROD версии
            // 'enabled' => YII_ENV_PROD,
            // Идентификатор компонента `\yii\web\User`
            // 'user' => 'user',
            // Поле для отображения имени из модели пользователя
            // 'userNameAttribute' => 'username',
            // Хранилище для логов, реализует `\lav45\activityLogger\StorageInterface`
        ],

        /**
         * Хранилище для логов, реализует `\lav45\activityLogger\StorageInterface`
         */
        'activityLoggerStorage' => [
            '__class' => \lav45\activityLogger\storage\DbStorage::class,
            // Имя таблицы в которой будут храниться логи
            // 'tableName' => '{{%activity_log}}',
            // Идентификатор компонента `\yii\db\Connection`
            // 'db' => 'db',
        ],
    ]
];
```

Значения по умолчанию для всех лог записей можно задать через `Yii::$container`
Для удобства этот код можно разместить в файле `bootstrap.php`

```php
Yii::$container->set(\lav45\activityLogger\storage\MessageData::class, [
    'env' => 'console', // Окружение из которого производилось действие
    'userId' => 'console',
    'userName' => 'Droid R2-D2',
]);
```

### Ссылки для просмотра логов

```php
// На этой странице можно просмотреть все логи
Url::toRoute(['/logger/default/index']);

// На этой странице можно просмотреть журналы действий конкретного пользователя по его `$id`
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
                '__class' => \lav45\activityLogger\ActiveLogBehavior::class,
             
                // Если необходимо изменить стандартное значение `entityName`
                'getEntityName' => function () {
                    return static::tableName();
                },
                // Если необходимо изменить стандартное значение `entityId`
                'getEntityId' => function () {
                    return $this->getPrimaryKey();
                }
                /** 
                 * В случаях когда нужно для конкретного ActiveLogBehavior сделать подпись с понятным названием.
                 * Если на странице выводится история изменений всех пользователей,  
                 * не всегда понятно, у кого именно изменился статус, день рождения или другие данные
                 */
                'beforeSaveMessage' => function ($data) {
                    return ['attribute' => 'custom data'] + $data;
                }
                
                // Список полей, за изменением которых нужно следить
                'attributes' => [
                    // Простые поля ( string|int|bool )
                    'name',

                    // Поля, значение которых можно найти в списке.
                    // в данном случае `$model->getStatusList()[$model->status]`
                    'status' => [
                        'list' => 'statusList',
                    ],

                    // Поле, значение которого является `id` связи с другой моделью
                    'template_id' => [
                        'relation' => 'template',
                        // Поле из связанной таблицы, которое будет использовано в качестве отображаемого значения
                        'attribute' => 'name',
                    ],
                ]
            ],
            [
                /**
                 * В случаях когда нужно для всех логов делать подпись с понятным названием.
                 * Если на странице выводятся история изменения всех пользователей,  
                 * не всегда понятно, у кого именно изменился статус, день рождения или другие данные
                 */
                '__class' => \lav45\activityLogger\LogInfoBehavior::class,
                'template' => '{username} ({profile.email})',
            ],
        ];
    }

    /**
     * Если необходимо форматировать отображаемое значение,
     * можно указать любой поддерживаемый формат компонентом `\yii\i18n\Formatter`
     * или использовать произвольную функцию
     * @return array
     */
    public function attributeFormats()
    {
        return [
            // Значение атрибута будет форматироваться с помощью Yii::$app->formatter->asDatetime($value);
            'published_at' => 'datetime',

            // Можно использовать свою функцию обратного вызова 
            'is_published' => static function($value) {
                return Yii::$app->formatter->asBoolean($value);
            },

            // Если нужно вывести имени картинки и ссылку на неё
            'image' => static function($value) {
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
        $this->on(ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE, static function (\lav45\activityLogger\MessageEvent $event) {
            // Вы можете добавить в список логов свою информацию
            $event->logData[] = 'Reset password';
        });

        $this->on(ActiveLogBehavior::EVENT_AFTER_SAVE_MESSAGE, static function (\yii\base\Event $event) {
            // Какие-то действия после записи логов
        });
    }

    /*
     * Вместо регистрации события вы можете создать одноименный метод, который будет вызываться вместо события
     */

    /**
     * Будет вызываться вместо события [[ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE]]
     * @return array
     */
    public function beforeSaveMessage($data)
    {
        // Вы можете добавить в список логов свою информацию
        $data[] = 'Reset password';

        // или заменить отображаемое значение в логах для атрибута `password_hash`
        $data['password_hash'] = 'Reset password';

        return $data;
    }

    /**
     * Будет вызываться вместо события [[ActiveLogBehavior::EVENT_AFTER_SAVE_MESSAGE]]
     */
    public function afterSaveMessage()
    {
        // Какие-то действия после записи логов
    }
}
```

## Добавим консольный контроллер для очистки логов

Это необязательное расширение. Если вы не планируете удалять устаревшие логи, можете пропустить этот пункт.

```php
return [
    'controllerMap' => [
        'logger' => [
            '__class' => \lav45\activityLogger\console\DefaultController::class
        ]
    ],
];
```

Если необходимо удалить старые логи, используйте консольный контроллер:

```bash
yii logger/clean --old-than=1y
# => Deleted 5 record(s) from the activity log.
```

### Параметры командной строки

* `--entity-id, -eid`: Идентификатор целевого объекта

* `--entity-name, -e`: Псевдоним имени целевого объекта

* `--user-id, -uid`: Идентификатор пользователя, который выполнил действие

* `--log-action, -a`: Действие, которое было произведено над объектом

* `--env`: Среда, из которой производилось действие

* `--old-than, -o`: Удаление старых данных. По умолчанию 1y.

  Допустимые значения:
    - 1h - старше 1 часа
    - 2d - старше 2-х дней
    - 3m - старше 3-х месяцев
    - 4y - старше 4 лет

## Ручное использование компонента

### Добавление логов

Пригодится в тех случаях, когда в процессе работы приложения не используются ActiveRecord модели.
Например, при отправке отчетов, скачивании файлов, работе с внешним API, логировании процесса работы консольного
контроллера и т.д

```php
$message = Yii::createObject([
    '__class' => \lav45\activityLogger\storage\MessageData::class,
    'createdAt' => time(),
    // имя сущности
    'entityName' => 'user',
    // id сущности с которой производится действие
    'entityId' => 10,
    // Действие которое сейчас выполняется
    'action' => 'download',
    // текст с описанием действия
    'data' => ['export data'],
]);

Yii::$app->activityLogger->log($message);
```

Когда в логах нужно оставить одну запись со списком выполненных действий, можно воспользоваться `LogCollection`

```php
use lav45\activityLogger\LogCollection;

$collection = new LogCollection(Yii::$app->activityLogger, 'entityName');

/**
 * Добавляем все необходимые записи
 */
$collection->addMessage('Created: 100');
$collection->addMessage('Updated: 100500');
$collection->addMessage('Deleted: 5');

/**
 * Сохраняем все собранные сообщения как одну запись в логах
 * После записи список логов будет очищен
 */
$collection->push(); // => true
```

## Тестирование

```
~$ ./build.sh
~$ ./composer update --prefer-dist
~$ ./composer phpunit
```

## Поддерживаемые версии

**Активно поддерживается только последняя версия.**

| Version | PHP Versions | Status      |
|---------|--------------|-------------|
| `2.x`   | `>=7.4`      | Active      |
| `1.x`   | `>=5.5`      | End of life |

## Лицензии

Для получения информации о лицензии проверьте файл [LICENSE.md](LICENSE.md).
