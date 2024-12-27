# Подключение к ActiveRecord моделям

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
                'getEntityName' => static::tableName(),
                // Если необходимо изменить стандартное значение `entityId`
                'getEntityId' => function () {
                    return $this->getPrimaryKey();
                }
                /** 
                 * В случаях когда нужно для конкретного ActiveLogBehavior сделать подпись с понятным названием.
                 * Если на странице выводится история изменений всех пользователей,  
                 * не всегда понятно, у кого именно изменился статус, день рождения или другие данные
                 */
                'beforeSaveMessage' => static function ($data) {
                    return ['attribute' => 'custom data'] + $data;
                }
                
                // Список полей, за изменением которых нужно следить
                'attributes' => [
                    // Простые поля ( string|int|bool )
                    'name',

                    // Поля, значение которых можно найти в списке.
                    // В данном примере `$model->getStatusList()[$model->status]`
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
