# Миграции

## Настраиваем

Для начала нужно настроить `MigrateController` таким образом, чтобы он получал миграции из нескольких источников.
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

## Запускаем

```bash
yii migrate
```