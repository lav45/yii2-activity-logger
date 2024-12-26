<?php

use yii\caching\MemCache;
use yii\console\Application;
use yii\db\Connection;

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

new Application([
    'id' => 'test',
    'basePath' => __DIR__,
    'components' => [
        'cache' => [
            '__class' => MemCache::class,
            'useMemcached' => extension_loaded('memcached'),
        ],
        'db' => [
            '__class' => Connection::class,
            'dsn' => 'sqlite::memory:',
        ]
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