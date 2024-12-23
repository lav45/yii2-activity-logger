<?php

namespace lav45\activityLogger\test\console;

use lav45\activityLogger\LogMessageDTO;
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

        $manager = $controller->getLogger();
        self::assertEquals($manager->old_than, $result[1]);
        self::assertEquals($manager->options, $result[0]);
    }

    public function getActionCleanDataProvider(): array
    {
        return [
            'entity-name' => [
                ['entity-name' => 'user'],
                [['entityName' => 'user'], strtotime('-1 year 0:00')],
            ],
            'e' => [
                ['_aliases' => ['e' => 'user']],
                [['entityName' => 'user'], strtotime('-1 year 0:00')],
            ],
            'entity-id' => [
                ['entity-id' => 10],
                [['entityId' => 10], strtotime('-1 year 0:00')],
            ],
            'eid' => [
                ['_aliases' => ['eid' => 10]],
                [['entityId' => 10], strtotime('-1 year 0:00')],
            ],
            'user-id' => [
                ['user-id' => 100],
                [['userId' => 100], strtotime('-1 year 0:00')],
            ],
            'uid' => [
                ['_aliases' => ['uid' => 100]],
                [['userId' => 100], strtotime('-1 year 0:00')],
            ],
            'log-action' => [
                ['log-action' => 'console'],
                [['action' => 'console'], strtotime('-1 year 0:00')],
            ],
            'a' => [
                ['_aliases' => ['a' => 'console']],
                [['action' => 'console'], strtotime('-1 year 0:00')],
            ],
            'old-than' => [
                ['old-than' => '2m'],
                [[], strtotime('-2 month 0:00')],
            ],
            'o' => [
                ['_aliases' => ['o' => '2m']],
                [[], strtotime('-2 month 0:00')],
            ],
            'all' => [
                [
                    'entity-name' => 'user',
                    'entity-id' => 10,
                    'user-id' => 100,
                    'log-action' => 'console',
                    'old-than' => '2m',
                ],
                [
                    [
                        'entityName' => 'user',
                        'entityId' => 10,
                        'userId' => 100,
                        'action' => 'console',
                    ],
                    strtotime('-2 month 0:00')
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
    public $result = true;

    public $old_than;

    public $options;

    public function delete(LogMessageDTO $message, $old_than = null)
    {
        $this->old_than = $old_than;
        $this->options = array_filter((array)$message);
        return $this->result;
    }
}