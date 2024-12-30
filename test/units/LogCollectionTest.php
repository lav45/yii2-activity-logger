<?php

namespace lav45\activityLogger\test\units;

use lav45\activityLogger\LogCollection;
use lav45\activityLogger\middlewares\EnvironmentMiddleware;
use lav45\activityLogger\middlewares\UserInterface;
use lav45\activityLogger\middlewares\UserMiddleware;
use lav45\activityLogger\test\components\FakeManager;
use PHPUnit\Framework\TestCase;

class LogCollectionTest extends TestCase
{
    public function testSetEntityId(): void
    {
        $logger = new FakeManager();
        $entityName = 'test';
        $collection = new LogCollection($logger, $entityName);

        $entityId = 10;
        $this->assertEquals($collection, $collection->setEntityId($entityId));

        $collection->addMessage('test message');
        $this->assertTrue($collection->push());

        $this->assertEquals($entityId, $logger->message->entityId);
        $this->assertEquals($entityName, $logger->message->entityName);
    }

    public function testSetAction(): void
    {
        $logger = new FakeManager();
        $collection = new LogCollection($logger, 'test');

        $action = 'sync';
        $this->assertEquals($collection, $collection->setAction($action));

        $collection->addMessage('Updated: 100500');
        $this->assertTrue($collection->push());

        $this->assertEquals($action, $logger->message->action);
    }

    public function testAddAndPushMessage(): void
    {
        $ent = 'console';
        $user = new class implements UserInterface {
            public function getId(): string
            {
                return 'console';
            }

            public function getName(): string
            {
                return 'Droid R2-D2';
            }
        };

        $logger = new FakeManager();
        $logger->middlewares = [
            new UserMiddleware($user),
            new EnvironmentMiddleware($ent),
        ];

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

        $this->assertEquals($ent, $logger->message->env);
        $this->assertEquals($user->getId(), $logger->message->userId);
        $this->assertEquals($user->getName(), $logger->message->userName);
        $this->assertEquals($messages, $logger->message->data);
    }
}
