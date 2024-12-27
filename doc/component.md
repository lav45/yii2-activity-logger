# Компоненты


Необходимо добавить в конфигурационный файл

```php
Yii::$container->setDefinitions([
    \lav45\activityLogger\ManagerInterface::class => static fn() => Instance::ensure('activityLogger'),
    \lav45\activityLogger\storage\StorageInterface::class => static fn() => Instance::ensure('activityLoggerStorage'),
]);

return [
    'components' => [
        /**
         * Компонент принимает и управляет логами
         */
        'activityLogger' => [
            '__class' => \lav45\activityLogger\Manager::class,

            // Если необходимо отключить логирование, можете использовать заглушку
            // '__class' => YII_ENV_PROD ?
            //      \lav45\activityLogger\Manager::class :
            //      \lav45\activityLogger\DummyManager::class,

            // Идентификатор компонента `\yii\web\User`
            // 'user' => 'user',

            // Поле для отображения имени из модели пользователя
            // 'userNameAttribute' => 'username',
            
            // В debug режиме, все Exception будут выбрасывать исключение,
            // иначе писать сообщение `Yii::error()` в логи.
            // 'debug' => YII_DEBUG
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