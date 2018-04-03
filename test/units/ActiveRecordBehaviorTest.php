<?php

namespace lav45\activityLogger\test\units;

use Yii;
use yii\base\Event;
use lav45\activityLogger\test\models\User;
use lav45\activityLogger\test\models\UserEventMethod;
use lav45\activityLogger\test\models\TestEntityName;
use lav45\activityLogger\modules\models\ActivityLog;
use lav45\activityLogger\ActiveRecordBehavior as ActiveLogBehavior;
use lav45\activityLogger\MessageEvent;
use PHPUnit\Framework\TestCase;

/**
 * Class ActiveRecordBehaviorTest
 * @package lav45\activityLogger\test\units
 */
class ActiveRecordBehaviorTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        Yii::$app->set('db', [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite::memory:',
        ]);
        Yii::$app->set('activityLogger', [
            'class' => 'lav45\activityLogger\Manager',
        ]);
        Yii::$app->set('activityLoggerStorage', [
            'class' => 'lav45\activityLogger\DbStorage',
        ]);

        Yii::$app->runAction('migrate/up', [
            'migrationPath' => __DIR__ . '/../../migrations',
            'interactive' => 0
        ]);
        Yii::$app->runAction('migrate/up', [
            'migrationPath' => __DIR__ . '/../migrations',
            'interactive' => 0
        ]);
    }

    /**
     * @return User
     */
    private function createModel()
    {
        $model = new User();
        $model->login = 'buster';
        $model->friend_count = '5';
        $model->salary = '100.500';
        $model->birthday = '01.01.2005';
        $model->company_id = '1';
        $model->save();
        return $model;
    }

    public function tearDown()
    {
        // To ensure that during the test, the base does not increase in size
        $command = Yii::$app->getDb()->createCommand();
        $command->truncateTable(User::tableName())->execute();
        $command->truncateTable(ActivityLog::tableName())->execute();
    }

    public function testCreateModelWithDefaultOptions()
    {
        $model = new User();
        $this->assertTrue($model->save());

        $expected = [
            'status' => [
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
            'is_hidden' => [
                'new' => [
                    'value' => false
                ]
            ]
        ];

        $this->assertEquals($expected, $model->getLastActivityLog()->getData());
    }

    public function testIsEmpty()
    {
        $model = new User();
        /** @var \lav45\activityLogger\ActiveRecordBehavior $logger */
        $logger = $model->getBehavior('logger');
        $logger->isEmpty = function ($value) {
            return empty($value);
        };

        $this->assertTrue($model->save());

        $expected = [
            'status' => [
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
        ];

        $this->assertEquals($expected, $model->getLastActivityLog()->getData());
    }

    public function testCreateModelWithCustomOptions()
    {
        $model = $this->createModel();
        $logData = $model->getLastActivityLog();

        $this->assertTrue($logData->created_at > 0);
        $this->assertEquals('Создание', $logData->action);
        $this->assertEquals('user', $logData->entity_name);
        $this->assertEquals($model->getPrimaryKey(), $logData->entity_id);

        $expected = [
            'status' => [
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
            'login' => [
                'new' => [
                    'value' => 'buster'
                ]
            ],
            'is_hidden' => [
                'new' => [
                    'value' => false
                ]
            ],
            'friend_count' => [
                'new' => [
                    'value' => 5
                ]
            ],
            'salary' => [
                'new' => [
                    'value' => 100.5
                ]
            ],
            'birthday' => [
                'new' => [
                    'value' => '01.01.2005'
                ]
            ],
            'company_id' => [
                'new' => [
                    'value' => 'Asus',
                    'id' => 1
                ]
            ]
        ];

        $this->assertEquals($expected, $logData->getData());
    }

    public function testSaveWithoutUpdateAttributes()
    {
        $model = $this->createModel();

        $oldLogs = $model->getLastActivityLog()->getData();
        $model->save();
        $newLogs = $model->getLastActivityLog()->getData();

        $this->assertEquals($oldLogs, $newLogs);
    }

    /**
     * @dataProvider updateModelDataProvider
     * @param array $values
     * @param array $expected
     */
    public function testUpdateModel(array $values, array $expected)
    {
        $model = $this->createModel();
        $model->setAttributes($values);
        $this->assertTrue($model->save());
        $logModel = $model->getLastActivityLog();
        $this->assertEquals('Изменение', $logModel->action);
        $this->assertEquals($expected, $logModel->getData());
    }

    public function updateModelDataProvider()
    {
        return [
            'update login' => [
                ['login' => 'nimbus'],
                [
                    'login' => [
                        'old' => [
                            'value' => 'buster'
                        ],
                        'new' => [
                            'value' => 'nimbus'
                        ]
                    ]
                ]
            ],
            'update is_hidden' => [
                ['is_hidden' => true],
                [
                    'is_hidden' => [
                        'old' => [
                            'value' => false
                        ],
                        'new' => [
                            'value' => true
                        ]
                    ]
                ]
            ],
            'update friend_count' => [
                ['friend_count' => 15],
                [
                    'friend_count' => [
                        'old' => [
                            'value' => 5
                        ],
                        'new' => [
                            'value' => 15
                        ]
                    ]
                ]
            ],
            'update salary' => [
                ['salary' => 150.3],
                [
                    'salary' => [
                        'old' => [
                            'value' => 100.5
                        ],
                        'new' => [
                            'value' => 150.3
                        ]
                    ]
                ]
            ],
            'update birthday' => [
                ['birthday' => '03.03.2005'],
                [
                    'birthday' => [
                        'old' => [
                            'value' => '01.01.2005'
                        ],
                        'new' => [
                            'value' => '03.03.2005'
                        ]
                    ]
                ]
            ],
            'update status' => [
                ['status' => User::STATUS_DISABLED],
                [
                    'status' => [
                        'old' => [
                            'value' => 'Active',
                            'id' => User::STATUS_ACTIVE
                        ],
                        'new' => [
                            'value' => 'Disabled',
                            'id' => User::STATUS_DISABLED
                        ]
                    ]
                ]
            ],
            'update company_id' => [
                ['company_id' => 2],
                [
                    'company_id' => [
                        'old' => [
                            'value' => 'Asus',
                            'id' => 1
                        ],
                        'new' => [
                            'value' => 'HP',
                            'id' => 2
                        ]
                    ]
                ]
            ],
            'update salary and company_id' => [
                [
                    'company_id' => 2,
                    'salary' => 150.3
                ],
                [
                    'salary' => [
                        'old' => [
                            'value' => 100.5
                        ],
                        'new' => [
                            'value' => 150.3
                        ]
                    ],
                    'company_id' => [
                        'old' => [
                            'value' => 'Asus',
                            'id' => 1
                        ],
                        'new' => [
                            'value' => 'HP',
                            'id' => 2
                        ]
                    ]
                ]
            ],
        ];
    }

    public function testDeleteModel()
    {
        $model = $this->createModel();
        $this->assertEquals(1, $model->delete());

        $this->assertNotNull($model->company);

        $logModels = ActivityLog::findAll([
            'entity_name' => $model->getEntityName(),
            'entity_id' => $model->getEntityId(),
        ]);

        $this->assertEquals(1, count($logModels));

        $expected = [
            'status' => [
                'old' => [
                    'id' => 10,
                    'value' => 'Active'
                ],
                'new' => [
                    'id' => null,
                    'value' => null
                ]
            ],
            'company_id' => [
                'old' => [
                    'id' => 1,
                    'value' => 'Asus'
                ],
                'new' => [
                    'id' => null,
                    'value' => null
                ]
            ],
            'login' => [
                'old' => [
                    'value' => 'buster'
                ],
                'new' => [
                    'value' => null
                ]
            ],
            'is_hidden' => [
                'old' => [
                    'value' => false
                ],
                'new' => [
                    'value' => null
                ]
            ],
            'friend_count' => [
                'old' => [
                    'value' => 5
                ],
                'new' => [
                    'value' => null
                ]
            ],
            'salary' => [
                'old' => [
                    'value' => 100.5
                ],
                'new' => [
                    'value' => null
                ]
            ],
            'birthday' => [
                'old' => [
                    'value' => '01.01.2005'
                ],
                'new' => [
                    'value' => null
                ]
            ]
        ];
        $this->assertEquals($expected, $logModels[0]->getData());
        $this->assertEquals('Удаление', $logModels[0]->action);
    }

    public function testSoftDelete()
    {
        $model = $this->createModel();
        /** @var \lav45\activityLogger\ActiveRecordBehavior $logger */
        $logger = $model->getBehavior('logger');
        $logger->softDelete = true;

        $this->assertEquals(1, $model->delete());

        $logModels = ActivityLog::findAll([
            'entity_name' => $model->getEntityName(),
            'entity_id' => $model->getEntityId(),
        ]);

        $this->assertEquals(2, count($logModels));

        $expected = [
            'status' => [
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
            'company_id' => [
                'new' => [
                    'id' => 1,
                    'value' => 'Asus'
                ]
            ],
            'login' => [
                'new' => [
                    'value' => 'buster'
                ]
            ],
            'is_hidden' => [
                'new' => [
                    'value' => false
                ]
            ],
            'friend_count' => [
                'new' => [
                    'value' => 5
                ]
            ],
            'salary' => [
                'new' => [
                    'value' => 100.5
                ]
            ],
            'birthday' => [
                'new' => [
                    'value' => '01.01.2005'
                ]
            ]
        ];
        $this->assertEquals($expected, $logModels[0]->getData());
        $this->assertEquals('Создание', $logModels[0]->action);

        $expected = [
            'status' => [
                'old' => [
                    'id' => 10,
                    'value' => 'Active'
                ],
                'new' => [
                    'id' => null,
                    'value' => null
                ]
            ],
            'company_id' => [
                'old' => [
                    'id' => 1,
                    'value' => 'Asus'
                ],
                'new' => [
                    'id' => null,
                    'value' => null
                ]
            ],
            'login' => [
                'old' => [
                    'value' => 'buster'
                ],
                'new' => [
                    'value' => null
                ]
            ],
            'is_hidden' => [
                'old' => [
                    'value' => false
                ],
                'new' => [
                    'value' => null
                ]
            ],
            'friend_count' => [
                'old' => [
                    'value' => 5
                ],
                'new' => [
                    'value' => null
                ]
            ],
            'salary' => [
                'old' => [
                    'value' => 100.5
                ],
                'new' => [
                    'value' => null
                ]
            ],
            'birthday' => [
                'old' => [
                    'value' => '01.01.2005'
                ],
                'new' => [
                    'value' => null
                ]
            ]
        ];
        $this->assertEquals($expected, $logModels[1]->getData());
        $this->assertEquals('Удаление', $logModels[1]->action);
    }

    public function testLogEmptyAttributeAfterDeleteModel()
    {
        $model = new User();
        $model->birthday = '01.01.2005';
        $this->assertTrue($model->save());

        $expected = [
            'birthday' => [
                'new' => [
                    'value' => '01.01.2005'
                ]
            ],
            'status' => [
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
            'is_hidden' => [
                'new' => [
                    'value' => false
                ]
            ],
        ];

        $this->assertEquals($expected, $model->getLastActivityLog()->getData());

        $this->assertEquals(1, $model->delete());

        $logModels = ActivityLog::findAll([
            'entity_name' => $model->getEntityName(),
            'entity_id' => $model->getEntityId(),
        ]);

        $this->assertEquals(1, count($logModels));

        $expected = [
            'birthday' => [
                'old' => [
                    'value' => '01.01.2005'
                ],
                'new' => [
                    'value' => null
                ]
            ],
            'status' => [
                'old' => [
                    'id' => 10,
                    'value' => 'Active'
                ],
                'new' => [
                    'id' => null,
                    'value' => null
                ]
            ],
            'is_hidden' => [
                'old' => [
                    'value' => false
                ],
                'new' => [
                    'value' => null
                ]
            ],
        ];

        $this->assertEquals($expected, $logModels[0]->getData());
    }

    public function testGetEntityName()
    {
        $model = $this->createModel();
        /** @var \lav45\activityLogger\ActiveRecordBehavior $logger */
        $logger = $model->getBehavior('logger');

        $this->assertEquals('user', $logger->getEntityName());

        $new_entity_name = 'custom entity name';
        $logger->getEntityName = function () use ($new_entity_name) {
            return $new_entity_name;
        };

        $this->assertEquals($new_entity_name, $logger->getEntityName());

        $testModel = new TestEntityName();
        $this->assertEquals('test_entity_name', $testModel->getEntityName());
    }

    public function testDefaultGetEntityId()
    {
        $model = $this->createModel();
        /** @var \lav45\activityLogger\ActiveRecordBehavior $logger */
        $logger = $model->getBehavior('logger');

        $this->assertEquals($model->getPrimaryKey(), $logger->getEntityId());
    }

    /**
     * @expectedException \yii\base\InvalidValueException
     */
    public function testExceptionGetEntityId()
    {
        $testModel = new TestEntityName();
        $testModel->getEntityId();
    }

    /**
     * @dataProvider customGetEntityIdDataProvider
     * @param string|int|array $custom_entity_id
     * @param string $result_entity_id
     */
    public function testCustomGetEntityId($custom_entity_id, $result_entity_id)
    {
        $model = $this->createModel();
        /** @var \lav45\activityLogger\ActiveRecordBehavior $logger */
        $logger = $model->getBehavior('logger');

        $logger->getEntityId = function () use ($custom_entity_id) {
            return $custom_entity_id;
        };

        $this->assertEquals($result_entity_id, $logger->getEntityId());
    }

    public function customGetEntityIdDataProvider()
    {
        return [
            [1, 1],
            ['a', 'a'],
            [
                [
                    'id' => 10,
                    'type' => 1,
                ],
                '{"id":10,"type":1}'
            ]
        ];
    }

    public function testDisabledLoggerBeforeStart()
    {
        /** @var \lav45\activityLogger\Manager $logger */
        $logger = Yii::$app->get('activityLogger');
        $logger->enabled = false;

        $model = $this->createModel();
        $model->setAttributes([
            'company_id' => 2,
            'salary' => 150.3
        ]);
        $this->assertTrue($model->save());
        $this->assertNull($model->getLastActivityLog());

        // Reset component settings
        Yii::$app->set('activityLogger', [
            'class' => 'lav45\activityLogger\Manager',
        ]);
    }

    public function testDisabledLoggerAfterStart()
    {
        $model = $this->createModel();

        /** @var \lav45\activityLogger\Manager $logger */
        $logger = Yii::$app->get('activityLogger');
        $logger->delete($model->getEntityName());
        $logger->enabled = false;

        $model->setAttributes([
            'company_id' => 2,
            'salary' => 150.3
        ]);
        $this->assertTrue($model->save());
        $this->assertNull($model->getLastActivityLog());

        // Reset component settings
        Yii::$app->set('activityLogger', [
            'class' => 'lav45\activityLogger\Manager',
        ]);
    }

    public function testEventSaveMessage()
    {
        // Create
        $model = new User();
        $model->login = 'buster';

        $model->on(ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE,
            function (MessageEvent $event) use (&$beforeSaveFlag) {
                $event->append['action'] = 'Custom action';
                $beforeSaveFlag = true;
            });

        $model->on(ActiveLogBehavior::EVENT_AFTER_SAVE_MESSAGE,
            function (Event $event) use (&$afterSaveFlag) {
                $afterSaveFlag = true;
            });

        $beforeSaveFlag = false;
        $afterSaveFlag = false;
        $this->assertTrue($model->save());
        $this->assertTrue($afterSaveFlag);
        $this->assertTrue($beforeSaveFlag);

        $expected = [
            'status' => [
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
            'is_hidden' => [
                'new' => [
                    'value' => false
                ]
            ],
            'login' => [
                'new' => [
                    'value' => 'buster'
                ]
            ],
            'action' => 'Custom action',
        ];

        $this->assertNotNull($model->getLastActivityLog());
        $this->assertEquals($expected, $model->getLastActivityLog()->getData());

        // Update
        $model->login = 'buster2';

        $beforeSaveFlag = false;
        $afterSaveFlag = false;
        $this->assertTrue($model->save());
        $this->assertTrue($afterSaveFlag);
        $this->assertTrue($beforeSaveFlag);

        $expected = [
            'login' => [
                'old' => [
                    'value' => 'buster'
                ],
                'new' => [
                    'value' => 'buster2'
                ],
            ],
            'action' => 'Custom action',
        ];

        $this->assertEquals($expected, $model->getLastActivityLog()->getData());

        // Save without change
        $beforeSaveFlag = false;
        $afterSaveFlag = false;
        $this->assertTrue($model->save());
        $this->assertFalse($afterSaveFlag);
        $this->assertFalse($beforeSaveFlag);
    }

    public function testEventSaveMessageMethod()
    {
        // Create
        $model = new UserEventMethod();
        $model->login = 'buster';

        $model->appendLogs = [
            'event' => 'save message',
        ];

        $model->beforeSaveFlag = false;
        $model->afterSaveFlag = false;
        $this->assertTrue($model->save());
        $this->assertTrue($model->afterSaveFlag);
        $this->assertTrue($model->beforeSaveFlag);

        $expected = [
            'status' => [
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
            'is_hidden' => [
                'new' => [
                    'value' => false
                ]
            ],
            'login' => [
                'new' => [
                    'value' => 'buster'
                ]
            ],
            'event' => 'save message',
        ];

        $this->assertNotNull($model->getLastActivityLog());
        $this->assertEquals($expected, $model->getLastActivityLog()->getData());

        // Update
        $model->login = 'buster2';

        $model->beforeSaveFlag = false;
        $model->afterSaveFlag = false;
        $this->assertTrue($model->save());
        $this->assertTrue($model->afterSaveFlag);
        $this->assertTrue($model->beforeSaveFlag);

        $expected = [
            'login' => [
                'old' => [
                    'value' => 'buster'
                ],
                'new' => [
                    'value' => 'buster2'
                ],
            ],
            'event' => 'save message',
        ];

        $this->assertEquals($expected, $model->getLastActivityLog()->getData());

        // Save without change
        $model->beforeSaveFlag = false;
        $model->afterSaveFlag = false;
        $this->assertTrue($model->save());
        $this->assertFalse($model->afterSaveFlag);
        $this->assertFalse($model->beforeSaveFlag);
    }
}