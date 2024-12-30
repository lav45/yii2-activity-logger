<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\module;

use Yii;
use yii\i18n\PhpMessageSource;

class Module extends \yii\base\Module
{
    /**
     * @var array Список моделей которые логировались
     * [ entityName => \namespace\to\Model\EntityClass ]
     * Эта информация используется для корректного отображения имен полей, записанных данных
     * Если `entityName` не будет найдена в списке то имена полей будут выводится без преобразования
     * @see Model::getAttributeLabel()
     */
    public array $entityMap = [];

    /**
     * Initializes the module.
     */
    public function init()
    {
        parent::init();

        $this->initTranslations();
    }

    protected function initTranslations()
    {
        Yii::$app->i18n->translations['lav45/logger'] = [
            '__class' => PhpMessageSource::class,
            'basePath' => __DIR__ . '/messages',
        ];
    }
}
