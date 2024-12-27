# Настройка MessageData

Значения по умолчанию для всех лог записей можно задать через `Yii::$container`
Для удобства этот код можно разместить в файле `bootstrap.php`

```php
Yii::$container->set(\lav45\activityLogger\storage\MessageData::class, [
    'env' => 'console', // Окружение из которого производилось действие
    'userId' => 'console',
    'userName' => 'Droid R2-D2',
]);
```
