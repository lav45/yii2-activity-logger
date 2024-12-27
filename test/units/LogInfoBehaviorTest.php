<?php

namespace lav45\activityLogger\test\units;

use lav45\activityLogger\ActiveLogBehavior;
use lav45\activityLogger\LogInfoBehavior;
use lav45\activityLogger\MessageEvent;
use lav45\activityLogger\test\models\LogInfoModel;
use PHPUnit\Framework\TestCase;

class LogInfoBehaviorTest extends TestCase
{
    public function testEmptyTemplate(): void
    {
        $model = new LogInfoModel();
        $event = new MessageEvent();

        $expected = $event->logData;
        $model->trigger(ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE, $event);
        $this->assertEquals($expected, $event->logData);
    }

    public function testStringTemplate(): void
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

    public function testClosureTemplate(): void
    {
        $model = new LogInfoModel();
        /** @var LogInfoBehavior $behavior */
        $behavior = $model->getBehavior('logInfo');
        $behavior->template = static function () use ($model) {
            return "{$model->username} ({$model->profile['email']})";
        };

        $expected = ["{$model->username} ({$model->profile['email']})"];

        $event = new MessageEvent();
        $model->trigger(ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE, $event);

        $this->assertEquals($expected, $event->logData);
    }

    public function testAppendPrependLog(): void
    {
        $model = new LogInfoModel();
        /** @var LogInfoBehavior $behavior */
        $behavior = $model->getBehavior('logInfo');
        $behavior->template = '{username} ({profile.email})';

        $event = new MessageEvent();
        $event->logData = ['first log action'];

        $expected = [
            "{$model->username} ({$model->profile['email']})",
            'first log action',
        ];

        $model->trigger(ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE, $event);
        $this->assertEquals($expected, $event->logData);

        $behavior->prepend = false;
        $event->logData = ['first log action'];

        $expected = [
            'first log action',
            "{$model->username} ({$model->profile['email']})",
        ];

        $model->trigger(ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE, $event);
        $this->assertEquals($expected, $event->logData);
    }
}
