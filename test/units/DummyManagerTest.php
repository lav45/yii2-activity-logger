<?php

namespace lav45\activityLogger\test\units;

use lav45\activityLogger\DummyManager;
use lav45\activityLogger\storage\DeleteCommand;
use lav45\activityLogger\storage\MessageData;
use PHPUnit\Framework\TestCase;
use Yii;

class DummyManagerTest extends TestCase
{
    public function testCreate(): void
    {
        $manager = Yii::createObject([
            '__class' => DummyManager::class,
            'user' => 'user',
            'userNameAttribute' => 'username',
            'debug' => YII_DEBUG,
            'aaaa' => 123,
        ]);

        $this->assertInstanceOf(DummyManager::class, $manager);
        $this->assertNull($manager->user);
        $this->assertNull($manager->userNameAttribute);
        $this->assertNull($manager->debug);
        $this->assertNull($manager->aaaa);
        $this->assertFalse(isset($manager->aaaa));

        $this->assertFalse($manager->log(new MessageData()));
        $this->assertFalse($manager->delete(new DeleteCommand()));
    }
}