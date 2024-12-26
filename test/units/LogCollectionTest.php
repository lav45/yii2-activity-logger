<?php

namespace lav45\activityLogger\test\units;

use lav45\activityLogger\LogCollection;
use lav45\activityLogger\storage\MessageData;
use lav45\activityLogger\test\components\FakeManager;
use PHPUnit\Framework\TestCase;
use Yii;

class LogCollectionTest extends TestCase
{
    public function testSetEntityId(): void
    {
        $logger = new FakeManager();
        $entityName = 'test';
        $collection = new LogCollection($logger, $entityName);

        $entityId = 10;
        self::assertEquals($collection, $collection->setEntityId($entityId));

        $collection->addMessage('test message');
        $collection->push();

        self::assertEquals($entityId, $logger->message->entityId);
        self::assertEquals($entityName, $logger->message->entityName);
    }

    public function testSetAction(): void
    {
        $logger = new FakeManager();
        $collection = new LogCollection($logger, 'test');

        $action = 'sync';
        self::assertEquals($collection, $collection->setAction($action));

        $collection->addMessage('Updated: 100500');
        $collection->push();

        self::assertEquals($action, $logger->message->action);
    }

    public function testAddAndPushMessage(): void
    {
        $ent = 'console';
        $userId = 'console';
        $userName = 'Droid R2-D2';

        $oldContainer = clone Yii::$container;
        Yii::$container->set(MessageData::class, [
            'env' => $ent,
            'userId' => $userId,
            'userName' => $userName,
        ]);

        $logger = new FakeManager();
        $collection = new LogCollection($logger, 'test');

        $messages = [
            'Created: 100',
            'Updated: 100500',
            'Deleted: 5',
        ];

        foreach ($messages as $message) {
            $collection->addMessage($message);
        }

        self::assertTrue($collection->push());
        self::assertFalse($collection->push());

        self::assertEquals($ent, $logger->message->env);
        self::assertEquals($userId, $logger->message->userId);
        self::assertEquals($userName, $logger->message->userName);
        self::assertEquals($messages, $logger->message->data);

        Yii::$container = $oldContainer;
    }
}
