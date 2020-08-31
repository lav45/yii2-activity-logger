<?php

namespace lav45\activityLogger\test\units;

use lav45\activityLogger\LogCollection;
use lav45\activityLogger\test\components\FakeManager;
use PHPUnit\Framework\TestCase;

/**
 * Class LogCollectionTest
 * @package lav45\activityLogger\test\units
 */
class LogCollectionTest extends TestCase
{
    public function testSetEntityId()
    {
        $logger = new FakeManager();
        $collection = new LogCollection($logger, 'test');

        $entityId = 10;
        self::assertEquals($collection, $collection->setEntityId($entityId));

        $collection->addMessage('test message');
        $collection->push();

        $logs = $logger->removeLogs();

        self::assertEquals($entityId, $logs[0]['entityId']);
    }

    public function testSetAction()
    {
        $logger = new FakeManager();
        $collection = new LogCollection($logger, 'test');

        $action = 'sync';
        self::assertEquals($collection, $collection->setAction($action));

        $collection->addMessage('Updated: 100500');
        $collection->push();

        $logs = $logger->removeLogs();

        self::assertEquals($action, $logs[0]['action']);
    }

    public function testAddAndPushMessage()
    {
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

        $logs = $logger->removeLogs();

        self::assertEquals($messages, $logs[0]['message']);
    }
}
