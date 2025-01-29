# Компоненты

Необходимо добавить в конфигурационный файл

```php
Yii::$container->setDefinitions([
    \lav45\activityLogger\ManagerInterface::class => static fn() => Yii::$app->get('activityLogger'),
    \lav45\activityLogger\storage\StorageInterface::class => static fn() => Yii::$app->get('activityLoggerStorage'),
    \lav45\activityLogger\middlewares\UserInterface::class => static fn() => Yii::$app->getUser()->getIdentity(),
]);

define('LOG_ENV', 'api');

return [
    'components' => [
        /**
         * Компонент принимает и управляет логами, реализует `\lav45\activityLogger\ManagerInterface`
         */
        'activityLogger' => [
            '__class' => \lav45\activityLogger\Manager::class,
            'middlewares' => [
                [
                    'class' => \lav45\activityLogger\middlewares\UserMiddleware::class,
                ],
                [
                    'class' => \lav45\activityLogger\middlewares\EnvironmentMiddleware::class,
                    '__construct()' => [ 'env' => LOG_ENV ],
                ],
            ],

            // В debug режиме, все Exception будут выбрасывать исключение,
            // иначе писать сообщение `Yii::error()` в логи.
            // 'debug' => YII_DEBUG
        ],

        /**
         * Хранилище для логов, реализует `\lav45\activityLogger\StorageInterface`
         */
        'activityLoggerStorage' => [
            '__class' => \lav45\activityLogger\storage\DbStorage::class,

            // Если необходимо отключить логирование, можете использовать заглушку
            // '__class' => YII_ENV_PROD ?
            //      \lav45\activityLogger\storage\DbStorage::class :
            //      \lav45\activityLogger\storage\ArrayStorage::class,

            // Имя таблицы в которой будут храниться логи
            // 'tableName' => '{{%activity_log}}',

            // Идентификатор компонента `\yii\db\Connection`
            // 'db' => 'db',
        ],
    ]
];
```
