Инструкция по обновлению ActivityLogger
=======================================

Этот файл содержит примечаний которые может нарушить работу компонента при обновлении из одной версии в другую. Хотя мы стараемся обеспечить обратную совместимость, насколько это возможно, иногда это не возможно, или приводит к существенному снижению производительности. Так же вы сможете следить за всеми критическими изменениями в нашем проекте.


Обновление с 1.5.2
------------------
* Немного доработано представление `src/modules/views/default/_item.php`
* При записи в лог пустой строки она будет отображаться как `Yii::$app->formatter->nullDisplay`
* Значение по умолчанию для `\lav45\activityLogger\ActiveRecordBehavior::$identicalAttributes` теперь `false`
* `\lav45\activityLogger\ActiveRecordBehavior` не будет писать в лог пустые значения. За проверку наличия непустых данных отвечает метод `ActiveRecordBehavior::isEmpty()`, работу которого можно скорректировать с помощью свойства `ActiveRecordBehavior::$isEmpty` передав ему свою функцию.


Обновление с 1.5.1
------------------
* Удалены методы `\lav45\activityLogger\LogMessage`
    * getEntityName()
    * setEntityName()
    * getEntityId()
    * setEntityId()
    * getCreatedAt()
    * setCreatedAt()
    * getUserId()
    * setUserId()
    * getUserName()
    * setUserName()
    * getAction()
    * setAction()
    * getEnv()
    * setEnv()
    
    В место этого будут использоваться публичные свойства.


Обновление с 1.5.0
------------------
* Для того чтобы переопределить метод `\lav45\activityLogger\ActiveRecordBehavior::getEntityName()` используйте параметр `\lav45\activityLogger\ActiveRecordBehavior::$getEntityName`. Пользовательская функция должна возвращать строку. 
* Для того чтобы переопределить метод `\lav45\activityLogger\ActiveRecordBehavior::getEntityId()` используйте параметр `\lav45\activityLogger\ActiveRecordBehavior::$getEntityId`. Пользовательская функция должна возвращать строку или массив.

```php
    public function behaviors()
    {
        return [
            [
                'class' => 'lav45\activityLogger\ActiveRecordBehavior',
             
                // Если необхадимо изменить стандартное значение `entityName`
                'getEntityName' => function () {
                    return 'global_news';
                },
                // Если необхадимо изменить стандартное значение `entityId`
                'getEntityId' => function () {
                    return $this->global_news_id;
                }
            ]
        ];
    }
```


Обновление с 1.4.0
------------------
* `\lav45\activityLogger\DbStorage`
    * Удалён метод `clean($date)`, а также из интерфейса `\lav45\activityLogger\StorageInterface::clean($date)`
    * Метод `delete($entityName, $entityId)` теперь принимает `delete(\lav45\activityLogger\LogMessage $message)`
* `\lav45\activityLogger\Manager`
    * Был удален метод `createMessage()`
    ```php
    Yii::$app->activityLogger
        ->createMessage($entityName, [
            'entityId' => $entityId,
            'data' => [$messageText],
            'action' => $action,
        ])
        ->save();
    ```
    В место него был добавлен доработан более простой аналог `log()`
    ```php
    Yii::$app->activityLogger->log($entityName, $messageText, $action, $entityId);
    ```


Обновление с 1.3.0
------------------
* Данные для `\lav45\activityLogger\modules\models\DataModel` теперь передаются через метод `setData(array $value)`
* Был удален `\lav45\activityLogger\StorageTrait`, а его код перенесен в `\lav45\activityLogger\Manager`
* Для переводов будет использоваться категория `lav45/logger` в место `app`
    ```php
    Yii::t('lav45/logger', $text);
    ```
* Для таблицы `activity_log` было добавлено поле `'id' => $this->bigPrimaryKey()` 


Обновление с 1.2.0
------------------
* `\lav45\activityLogger\DbStorage` теперь должен быть зарегистрирован в списке компонентов под именем `activityLoggerStorage` и реализовывать интерфейс `\lav45\activityLogger\StorageInterface`


Обновление с 1.1.0
------------------
* `\lav45\activityLogger\modules\models\ActivityLogViewModel::getUserName()` генерирует ссылку для текущей страницы используя метод `Url::current()`
* Изменилась фраза `'The setting <strong>{attribute}</strong> has ben changed'` на `<strong>{attribute}</strong> has ben changed`
* В файле представления `src/modules/views/default/_item.php` добавлено отображение ссылки для фильтрации фогов по конкретному пользователю `$model->getEntityName()`
* Была переименована папка `migrates` в `migrations`
* Метод `\lav45\activityLogger\modules\models\ActivityLog::getData()` теперь всегда будет возвращать массив
* `\lav45\activityLogger\modules\Module::$createUserUrl` был удален. В место него будет использоваться ссылка выполняющая роль фильтрации данных по конкретному пользователю.
* Параметр `\lav45\activityLogger\Manager::$user` может принимать только имя компонента зарегистрированного в приложении и соответствующего классу `\yii\web\User`


Обновление с 1.0.0
------------------
* Удалены интерфейсы `\lav45\activityLogger\contracts\ManagerInterface` и `\lav45\activityLogger\contracts\MessageInterface`
* `\lav45\activityLogger\contracts\StorageInterface` => `lav45\activityLogger\StorageInterface`
* Была добавлена первая рабочая версия приложения :)
