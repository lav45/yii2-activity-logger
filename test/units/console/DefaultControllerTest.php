<?php

namespace lav45\activityLogger\test\console;

use lav45\activityLogger\Manager;
use lav45\activityLogger\ManagerInterface;
use lav45\activityLogger\MessageBuilder;
use lav45\activityLogger\MessageBuilderInterface;
use lav45\activityLogger\storage\DbStorage;
use lav45\activityLogger\storage\DeleteCommand;
use lav45\activityLogger\storage\MessageData;
use PHPUnit\Framework\TestCase;
use yii\base\Module;

class DefaultControllerTest extends TestCase
{
    /**
     * @return array{DefaultController, FakeManager}
     */
    private function createController(): array
    {
        $logger = new FakeManager();
        $module = new Module('console');
        $controller = new DefaultController('logger', $module, $logger);
        return [$controller, $logger];
    }

    public function testFailRun(): void
    {
        $storage = new DbStorage();
        $logger = new Manager($storage);
        $module = new Module('console');
        $controller = new DefaultController('logger', $module, $logger);

        try {
            $controller->run('clean', ['old-than' => '2k']);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals("Invalid old-than value: '2k'. You can use one of the 1h, 2d, 3m or 4y", $e->getMessage());
        }

        try {
            $controller->run('clean', ['old-than' => '2m']);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals("Condition can't be empty", $e->getMessage());
        }

        try {
            $controller->run('clean', [
                'entity-name' => 'user',
                'old-than' => '2m',
            ]);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @dataProvider getActionCleanDataProvider
     * @param array $params
     * @param array $result
     */
    public function testActionClean(array $params, array $result): void
    {
        [$controller, $logger] = $this->createController();
        $controller->run('clean', $params);

        $this->assertEquals($logger->options, $result);
    }

    public function getActionCleanDataProvider(): array
    {
        return [
            'entity-name' => [
                ['entity-name' => 'user'],
                ['entityName' => 'user'],
            ],
            'e' => [
                ['_aliases' => ['e' => 'user']],
                ['entityName' => 'user'],
            ],
            'entity-id' => [
                ['entity-id' => 10],
                ['entityId' => '10'],
            ],
            'eid' => [
                ['_aliases' => ['eid' => '10']],
                ['entityId' => '10'],
            ],
            'user-id' => [
                ['user-id' => '100'],
                ['userId' => '100'],
            ],
            'uid' => [
                ['_aliases' => ['uid' => '100']],
                ['userId' => '100'],
            ],
            'log-action' => [
                ['log-action' => 'console'],
                ['action' => 'console'],
            ],
            'a' => [
                ['_aliases' => ['a' => 'console']],
                ['action' => 'console'],
            ],
            'old-than' => [
                ['old-than' => '2m'],
                [
                    'oldThan' => strtotime('-2 month 00:00:00')
                ],
            ],
            '1h' => [
                ['_aliases' => ['o' => '1h']],
                [
                    'oldThan' => strtotime('-1 hour 00:00:00')
                ],
            ],
            '1d' => [
                ['_aliases' => ['o' => '1d']],
                [
                    'oldThan' => strtotime('-1 day 00:00:00')
                ],
            ],
            '2m' => [
                ['_aliases' => ['o' => '2m']],
                [
                    'oldThan' => strtotime('-2 month 00:00:00')
                ],
            ],
            '3y' => [
                ['_aliases' => ['o' => '3y']],
                [
                    'oldThan' => strtotime('-3 year 00:00:00')
                ],
            ],
            'all' => [
                [
                    'entity-name' => 'user',
                    'entity-id' => '10',
                    'user-id' => '100',
                    'log-action' => 'console',
                    'old-than' => '2m',
                ],
                [
                    'entityName' => 'user',
                    'entityId' => '10',
                    'userId' => '100',
                    'action' => 'console',
                    'oldThan' => strtotime('-2 month 00:00:00')
                ],
            ]
        ];
    }

    public function testStdOutActionClean(): void
    {
        [$controller, $manager] = $this->createController();

        $manager->result = true;
        $controller->runAction('clean');
        $this->assertEquals("Successful clearing the logs.\n", $controller->stdout);

        $manager->result = false;
        $controller->runAction('clean');
        $this->assertEquals("Error while cleaning the logs.\n", $controller->stdout);

        try {
            $manager->result = false;
            $controller->runAction('clean', ['old-than' => '12']);
        } catch (\Exception $e) {
            $this->assertEquals("Invalid old-than value: '12'. You can use one of the 1h, 2d, 3m or 4y", $e->getMessage());
        }
    }
}

class DefaultController extends \lav45\activityLogger\console\DefaultController
{
    public $stderr;

    public $stdout;

    public function stderr($string)
    {
        $this->stderr = $string;
    }

    public function stdout($string)
    {
        $this->stdout = $string;
    }
}

class FakeManager implements ManagerInterface
{
    public bool $result = true;

    public array $options;

    public function delete(DeleteCommand $command): bool
    {
        $this->options = array_filter((array)$command);
        return $this->result;
    }

    public function log(MessageData $message): bool
    {
        return $this->result;
    }

    public function createMessageBuilder(string $entityName): MessageBuilderInterface
    {
        return new MessageBuilder($entityName);
    }
}