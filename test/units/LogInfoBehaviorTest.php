<?php

namespace lav45\activityLogger\test\units;

use yii\base\Model;
use lav45\activityLogger\MessageEvent;
use lav45\activityLogger\ActiveLogBehavior;
use lav45\activityLogger\LogInfoBehavior;
use PHPUnit\Framework\TestCase;

/**
 * Class LogInfoBehaviorTest
 * @package lav45\activityLogger\test\units
 */
class LogInfoBehaviorTest extends TestCase
{
    public function testEmptyTemplate()
    {
        $model = new LogInfoModel();
        $event = new MessageEvent();

        $expected = $event->logData;
        $model->trigger(ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE, $event);
        $this->assertEquals($expected, $event->logData);
    }

    public function testStringTemplate()
    {
        $model = new LogInfoModel();
        /** @var LogInfoBehavior $behavior */
        $behavior = $model->getBehavior('logInfo');
        $behavior->template = '{username} ({profile.email})';

        $expected = ["{$model->username} ({$model->profile['email']})"];

        $event = new MessageEvent();
        $model->trigger(ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE, $event);

        $this->assertEquals($expected, $event->logData);
    }

    public function testClosureTemplate()
    {
        $model = new LogInfoModel();
        /** @var LogInfoBehavior $behavior */
        $behavior = $model->getBehavior('logInfo');
        $behavior->template = function () use ($model) {
            return "{$model->username} ({$model->profile['email']})";
        };

        $expected = ["{$model->username} ({$model->profile['email']})"];

        $event = new MessageEvent();
        $model->trigger(ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE, $event);

        $this->assertEquals($expected, $event->logData);
    }

    public function testAppendPrependLog()
    {
        $model = new LogInfoModel();
        /** @var LogInfoBehavior $behavior */
        $behavior = $model->getBehavior('logInfo');
        $behavior->template = '{username} ({profile.email})';

        $event = new MessageEvent();
        $event->logData = ['first log action'];

        $expected = ["{$model->username} ({$model->profile['email']})"] + $event->logData;

        $model->trigger(ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE, $event);
        $this->assertEquals($expected, $event->logData);

        $behavior->prepend = false;
        $event->logData = ['first log action'];

        $expected = $event->logData;
        $expected[] = "{$model->username} ({$model->profile['email']})";

        $model->trigger(ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE, $event);
        $this->assertEquals($expected, $event->logData);
    }
}

/**
 * Class LogInfoModel
 * @package lav45\activityLogger\test\units
 * @property array $profile
 */
class LogInfoModel extends Model
{
    /**
     * @var string
     */
    public $username = 'David';

    /**
     * @return array
     */
    public function getProfile() {
        return [
            'email' => 'david@gmail.com'
        ];
    }

    public function behaviors()
    {
        return [
            'logInfo' => [
                'class' => LogInfoBehavior::class,
            ]
        ];
    }
}