<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

new \yii\console\Application([
    'id' => 'unit',
    'basePath' => __DIR__,
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite::memory:',
        ],
        'cache' => [
            'class' => 'yii\caching\MemCache',
            'useMemcached' => extension_loaded('memcached'),
        ],
        'activityLogger' => [
            'class' => 'lav45\activityLogger\Manager',
        ],
        'activityLoggerStorage' => [
            'class' => 'lav45\activityLogger\DbStorage',
        ],
    ]
]);

Yii::$app->runAction('migrate/up', [
    'migrationPath' => __DIR__ . '/../migrations',
    'interactive' => 0
]);

Yii::$app->runAction('migrate/up', [
    'migrationPath' => __DIR__ . '/migrations',
    'interactive' => 0
]);
