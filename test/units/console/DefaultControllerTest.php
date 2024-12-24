<?php

namespace lav45\activityLogger\test\console;

use lav45\activityLogger\DeleteCommand;
use PHPUnit\Framework\TestCase;
use yii\base\Module;

class DefaultControllerTest extends TestCase
{
    private function createController(): DefaultController
    {
        $module = new Module('console');
        $controller = new DefaultController('logger', $module);
        $controller->setLogger(new Manager);
        return $controller;
    }

    /**
     * @dataProvider getActionCleanDataProvider
     * @param array $params
     * @param array $result
     */
    public function testActionClean(array $params, array $result): void
    {
        $controller = $this->createController();
        $controller->run('clean', $params);

        $options = $controller->getLogger()->options;
        self::assertEquals($options, $result);
    }

    public function getActionCleanDataProvider(): array
    {
        return [
            'entity-name' => [
                ['entity-name' => 'user'],
                [
                    'entityName' => 'user',
                    'oldThan' => strtotime('-1 year 00:00:00')
                ],
            ],
            'e' => [
                ['_aliases' => ['e' => 'user']],
                [
                    'entityName' => 'user',
                    'oldThan' => strtotime('-1 year 00:00:00')
                ],
            ],
            'entity-id' => [
                ['entity-id' => 10],
                [
                    'entityId' => '10',
                    'oldThan' => strtotime('-1 year 00:00:00')
                ],
            ],
            'eid' => [
                ['_aliases' => ['eid' => '10']],
                [
                    'entityId' => '10',
                    'oldThan' => strtotime('-1 year 00:00:00')
                ],
            ],
            'user-id' => [
                ['user-id' => '100'],
                [
                    'userId' => '100',
                    'oldThan' => strtotime('-1 year 00:00:00')
                ],
            ],
            'uid' => [
                ['_aliases' => ['uid' => '100']],
                [
                    'userId' => '100',
                    'oldThan' => strtotime('-1 year 00:00:00')
                ],
            ],
            'log-action' => [
                ['log-action' => 'console'],
                [
                    'action' => 'console',
                    'oldThan' => strtotime('-1 year 00:00:00')
                ],
            ],
            'a' => [
                ['_aliases' => ['a' => 'console']],
                [
                    'action' => 'console',
                    'oldThan' => strtotime('-1 year 00:00:00')
                ],
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
        $controller = $this->createController();
        $manager = $controller->getLogger();

        $manager->result = true;
        $controller->runAction('clean');
        self::assertEquals("Successful clearing the logs.\n", $controller->stdout);

        $manager->result = false;
        $controller->runAction('clean');
        self::assertEquals("Error while cleaning the logs.\n", $controller->stdout);

        $manager->result = false;
        $controller->runAction('clean', ['old-than' => '12']);
        self::assertEquals("Invalid date format\n", $controller->stderr);
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

class Manager extends \lav45\activityLogger\Manager
{
    public bool $result = true;

    public array $options;

    public function delete(DeleteCommand $command): bool
    {
        $this->options = array_filter((array)$command);
        return $this->result;
    }
}