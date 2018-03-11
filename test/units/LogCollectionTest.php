<?php

namespace lav45\activityLogger\test\units;

use lav45\activityLogger\LogCollection;
use lav45\activityLogger\test\components\FakeLogger;
use PHPUnit\Framework\TestCase;

/**
 * Class LogCollectionTest
 * @package lav45\activityLogger\test\units
 */
class LogCollectionTest extends TestCase
{
    public function testSetEntityId()
    {
        /** @var \lav45\activityLogger\Manager|FakeLogger $logger */
        $logger = new FakeLogger();
        $collection = new LogCollection($logger, 'test');

        $entityId = 10;
        $this->assertEquals($collection, $collection->setEntityId($entityId));

        $collection->addMessage('test message');
        $collection->push();

        $logs = $logger->removeLogs();

        $this->assertEquals($entityId, $logs[0]['entityId']);
    }

    public function testSetAction()
    {
        /** @var \lav45\activityLogger\Manager|FakeLogger $logger */
        $logger = new FakeLogger();
        $collection = new LogCollection($logger, 'test');

        $action = 'sync';
        $this->assertEquals($collection, $collection->setAction($action));

        $collection->addMessage('Updated: 100500');
        $collection->push();

        $logs = $logger->removeLogs();

        $this->assertEquals($action, $logs[0]['action']);
    }

    public function testAddAndPushMessage()
    {
        /** @var \lav45\activityLogger\Manager|FakeLogger $logger */
        $logger = new FakeLogger();
        $collection = new LogCollection($logger, 'test');

        $messages = [
            'Created: 100',
            'Updated: 100500',
            'Deleted: 5',
        ];

        foreach ($messages as $message) {
            $collection->addMessage($message);
        }

        $this->assertTrue($collection->push());
        $this->assertFalse($collection->push());

        $logs = $logger->removeLogs();

        $this->assertEquals($messages, $logs[0]['message']);
    }
}
