<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\test\units\storage;

use lav45\activityLogger\storage\ArrayStorage;
use lav45\activityLogger\storage\DeleteCommand;
use lav45\activityLogger\storage\MessageData;
use PHPUnit\Framework\TestCase;

final class ArrayStorageTest extends TestCase
{
    public function testSave(): void
    {
        $storage = new ArrayStorage();

        $message = new MessageData();
        $message->entityName = 'test';
        $message->entityId = 1;

        $storage->save($message);

        $this->assertEquals($message, $storage->messages[0]);
    }

    public function testDelete(): void
    {
        $storage = new ArrayStorage();

        $command = new DeleteCommand();
        $command->entityName = 'test';
        $command->entityId = 1;

        $storage->delete($command);

        $this->assertEquals($command, $storage->commands[0]);
    }
}