<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\test\units;

use lav45\activityLogger\MessageBuilder;
use lav45\activityLogger\storage\MessageData;
use PHPUnit\Framework\TestCase;

class MessageBuilderTest extends TestCase
{
    public function testClone(): void
    {
        $b = new MessageBuilder('a');

        $a = $b->withEntityId('asd');

        $this->assertNotEquals($b->build(1)->entityId, $a->build(1)->entityId);

        $b = $b->withEntityId('123');

        $this->assertNotEquals($b->build(1)->entityId, $a->build(1)->entityId);
    }

    public function testBuild(): void
    {
        $a = new MessageData();
        $a->entityName = 'a';
        $a->createdAt = time();
        $a->entityId = 'b';
        $a->userId = '1';
        $a->userName = 'name';
        $a->action = 'action';
        $a->data = ['updated' => '1'];

        $b = (new MessageBuilder($a->entityName))
            ->withEntityId($a->entityId)
            ->withUserId($a->userId)
            ->withUserName($a->userName)
            ->withAction($a->action)
            ->withData($a->data)
            ->build($a->createdAt);

        $this->assertEquals($a, $b);
    }
}