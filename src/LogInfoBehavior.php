<?php

namespace lav45\activityLogger;

use yii\base\Behavior;
use yii\helpers\ArrayHelper;

/**
 * Class LogInfoBehavior
 * @package lav45\activityLogger
 *
 * ======================= Example usage ======================
 *  public function behaviors()
 *  {
 *      return [
 *          [
 *              'class' => 'lav45\activityLogger\LogInfoBehavior',
 *              'template' => '{username} ({profile.email})',
 *              // OR
 *              //'template' => function() {
 *              //    return "{$this->username} ({$this->profile->email})";
 *              //},
 *          ]
 *      ];
 *  }
 * ============================================================
 *
 * @since 1.6.0
 */
class LogInfoBehavior extends Behavior
{
    /**
     * @var string|\Closure information field that will be displayed at the beginning of the list of logs for more information.
     *
     * example: '{username} ({profile.email})'
     * result: 'Maxim (max@gmail.com)'
     * {username} is an attribute of the `owner` model
     * {profile.email} is the relations attribute of the `profile` model
     */
    public $template;
    /**
     * @var bool add log data to start
     */
    public $prepend = true;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE => 'beforeSave',
        ];
    }

    /**
     * @param MessageEvent $event
     */
    public function beforeSave(MessageEvent $event)
    {
        if ($data = $this->getInfoData()) {
            if ($this->prepend === true) {
                $event->logData = [$data] + $event->logData;
            } else {
                $event->logData[] = $data;
            }
        }
    }

    /**
     * @return string|null
     */
    protected function getInfoData()
    {
        if ($this->template === null) {
            return null;
        }
        if (is_callable($this->template)) {
            return call_user_func($this->template);
        }

        $callback = function ($matches) {
            return ArrayHelper::getValue($this->owner, $matches[1]);
        };

        return preg_replace_callback('/\\{([\w\._]+)\\}/', $callback, $this->template);
    }
}