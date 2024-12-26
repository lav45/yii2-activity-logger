<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

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
 *              '__class' => 'lav45\activityLogger\LogInfoBehavior',
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
     * add log data to start
     */
    public bool $prepend = true;

    public function events(): array
    {
        return [
            ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE => 'beforeSave',
        ];
    }

    public function beforeSave(MessageEvent $event): void
    {
        if ($data = $this->getInfoData()) {
            if (true === $this->prepend) {
                array_unshift($event->logData, $data);
            } else {
                $event->logData[] = $data;
            }
        }
    }

    protected function getInfoData(): ?string
    {
        if (null === $this->template) {
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