# Добавление логов

Пригодится в тех случаях, когда в процессе работы приложения не используются `ActiveRecord` модели.
Например, при отправке отчетов, скачивании файлов, работе с внешним API, логирование процесса работы из консольного
контроллера и т.д

```php
use lav45\activityLogger\storage\MessageData;

$message = Yii::createObject([
    '__class' => MessageData::class,
    // Дата создания записи в логах
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