<?php

namespace lav45\activityLogger\test\units;

use lav45\activityLogger\ActiveLogBehavior;
use lav45\activityLogger\DbStorage;
use lav45\activityLogger\LogMessageDTO;
use lav45\activityLogger\Manager;
use lav45\activityLogger\MessageEvent;
use lav45\activityLogger\modules\models\ActivityLog;
use lav45\activityLogger\test\models\LogUser as User;
use lav45\activityLogger\test\models\TestEntityName;
use lav45\activityLogger\test\models\UserEventMethod;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Event;

/**
 * Class ActiveLogBehaviorTest
 * @package lav45\activityLogger\test\units
 */
class ActiveLogBehaviorTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        Yii::$app->set('activityLogger', [
            'class' => Manager::class,
        ]);
        Yii::$app->set('activityLoggerStorage', [
            'class' => DbStorage::class,
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
        User::deleteAll();
        ActivityLog::deleteAll();
    }

    public function testCreateModelWithDefaultOptions()
    {
        $ent = 'console';
        $userId = 'console';
        $userName = 'Droid R2-D2';

        Yii::$container->set(LogMessageDTO::class, [
            'env' => $ent,
            'userId' => $userId,
            'userName' => $userName,
        ]);

        $model = new User();
        self::assertTrue($model->save());

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

        $activityLog = $model->getLastActivityLog();

        self::assertEquals($expected, $activityLog->getData());
        self::assertEquals($ent, $activityLog->env);
        self::assertEquals($userId, $activityLog->user_id);
        self::assertEquals($userName, $activityLog->user_name);
        self::assertEquals('created', $activityLog->action);
    }

    public function testIsEmpty()
    {
        $model = new User();
        /** @var ActiveLogBehavior $logger */
        $logger = $model->getBehavior('logger');
        $logger->isEmpty = function ($value) {
            return empty($value);
        };

        self::assertTrue($model->save());

        $expected = [
            'status' => [
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
        ];

        self::assertEquals($expected, $model->getLastActivityLog()->getData());
    }

    public function testCreateModelWithCustomOptions()
    {
        $model = $this->createModel();
        $logData = $model->getLastActivityLog();

        self::assertTrue($logData->created_at > 0);
        self::assertEquals('created', $logData->action);
        self::assertEquals('user', $logData->entity_name);
        self::assertEquals($model->getPrimaryKey(), $logData->entity_id);

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

        self::assertEquals($expected, $logData->getData());
    }

    public function testSaveWithoutUpdateAttributes()
    {
        $model = $this->createModel();

        $oldLogs = $model->getLastActivityLog()->getData();
        $model->save();
        $newLogs = $model->getLastActivityLog()->getData();

        self::assertEquals($oldLogs, $newLogs);
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
        self::assertTrue($model->save());
        $logModel = $model->getLastActivityLog();
        self::assertEquals('updated', $logModel->action);
        self::assertEquals($expected, $logModel->getData());
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
        self::assertEquals(1, $model->delete());

        self::assertNotNull($model->company);

        /** @var array $logModels */
        $logModels = ActivityLog::findAll([
            'entity_name' => $model->getEntityName(),
            'entity_id' => $model->getEntityId(),
        ]);

        self::assertCount(1, $logModels);

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
        self::assertEquals($expected, $logModels[0]->getData());
        self::assertEquals('deleted', $logModels[0]->action);
    }

    public function testSoftDelete()
    {
        $model = $this->createModel();
        /** @var ActiveLogBehavior $logger */
        $logger = $model->getBehavior('logger');
        $logger->softDelete = true;

        self::assertEquals(1, $model->delete());

        /** @var array $logModels */
        $logModels = ActivityLog::find()
            ->where([
                'entity_name' => $model->getEntityName(),
                'entity_id' => $model->getEntityId(),
            ])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        self::assertCount(2, $logModels);

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
        self::assertEquals($expected, $logModels[0]->getData());
        self::assertEquals('created', $logModels[0]->action);

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
        self::assertEquals($expected, $logModels[1]->getData());
        self::assertEquals('deleted', $logModels[1]->action);
    }

    public function testLogEmptyAttributeAfterDeleteModel()
    {
        $model = new User();
        $model->birthday = '01.01.2005';
        self::assertTrue($model->save());

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

        self::assertEquals($expected, $model->getLastActivityLog()->getData());

        self::assertEquals(1, $model->delete());

        /** @var ActivityLog[]|\Countable $logModels */
        $logModels = ActivityLog::findAll([
            'entity_name' => $model->getEntityName(),
            'entity_id' => $model->getEntityId(),
        ]);

        self::assertCount(1, $logModels);

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

        self::assertEquals($expected, $logModels[0]->getData());
    }

    public function testGetEntityName()
    {
        $model = $this->createModel();
        /** @var ActiveLogBehavior $logger */
        $logger = $model->getBehavior('logger');

        self::assertEquals('user', $logger->getEntityName());

        $new_entity_name = 'custom entity name';
        $logger->getEntityName = function () use ($new_entity_name) {
            return $new_entity_name;
        };

        self::assertEquals($new_entity_name, $logger->getEntityName());

        $testModel = new TestEntityName();
        self::assertEquals('test_entity_name', $testModel->getEntityName());
    }

    public function testDefaultGetEntityId()
    {
        $model = $this->createModel();
        /** @var ActiveLogBehavior $logger */
        $logger = $model->getBehavior('logger');

        self::assertEquals($model->getPrimaryKey(), $logger->getEntityId());
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
        /** @var ActiveLogBehavior $logger */
        $logger = $model->getBehavior('logger');

        $logger->getEntityId = function () use ($custom_entity_id) {
            return $custom_entity_id;
        };

        self::assertEquals($result_entity_id, $logger->getEntityId());
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
        /** @var Manager $logger */
        $logger = Yii::$app->get('activityLogger');
        $logger->enabled = false;

        $model = $this->createModel();
        $model->setAttributes([
            'company_id' => 2,
            'salary' => 150.3
        ]);
        self::assertTrue($model->save());
        self::assertNull($model->getLastActivityLog());

        // Reset component settings
        Yii::$app->set('activityLogger', [
            'class' => Manager::class,
        ]);
    }

    public function testDisabledLoggerAfterStart()
    {
        $model = $this->createModel();

        /** @var Manager $logger */
        $logger = Yii::$app->get('activityLogger');
        $logger->delete(new LogMessageDTO([
            'entityName' => $model->getEntityName(),
        ]));
        $logger->enabled = false;

        $model->setAttributes([
            'company_id' => 2,
            'salary' => 150.3
        ]);
        self::assertTrue($model->save());
        self::assertNull($model->getLastActivityLog());

        // Reset component settings
        Yii::$app->set('activityLogger', [
            'class' => Manager::class,
        ]);
    }

    public function testEventSaveMessage()
    {
        // Create
        $model = new User();
        $model->login = 'buster';

        $model->on(ActiveLogBehavior::EVENT_BEFORE_SAVE_MESSAGE,
            function (MessageEvent $event) use (&$beforeSaveFlag) {
                $event->logData = ['info' => 'Custom info'] + $event->logData;
                $event->logData['action'] = 'Custom action';
                $beforeSaveFlag = true;
            });

        $model->on(ActiveLogBehavior::EVENT_AFTER_SAVE_MESSAGE,
            function ($event) use (&$afterSaveFlag) {
                self::assertInstanceOf(Event::class, $event);
                $afterSaveFlag = true;
            });

        $beforeSaveFlag = false;
        $afterSaveFlag = false;
        self::assertTrue($model->save());
        self::assertTrue($afterSaveFlag);
        self::assertTrue($beforeSaveFlag);

        $expected = [
            'info' => 'Custom info',
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

        self::assertNotNull($model->getLastActivityLog());
        self::assertEquals($expected, $model->getLastActivityLog()->getData());

        // Update
        $model->login = 'buster2';

        $beforeSaveFlag = false;
        $afterSaveFlag = false;
        self::assertTrue($model->save());
        self::assertTrue($afterSaveFlag);
        self::assertTrue($beforeSaveFlag);

        $expected = [
            'info' => 'Custom info',
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

        self::assertEquals($expected, $model->getLastActivityLog()->getData());

        // Save without change
        $beforeSaveFlag = false;
        $afterSaveFlag = false;
        self::assertTrue($model->save());
        self::assertFalse($afterSaveFlag);
        self::assertFalse($beforeSaveFlag);
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
        self::assertTrue($model->save());
        self::assertTrue($model->afterSaveFlag);
        self::assertTrue($model->beforeSaveFlag);

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

        $activityLog = $model->getLastActivityLog();

        self::assertNotNull($activityLog);
        self::assertEquals($expected, $activityLog->getData());

        // Update
        $model->login = 'buster2';

        $model->beforeSaveFlag = false;
        $model->afterSaveFlag = false;
        self::assertTrue($model->save());
        self::assertTrue($model->afterSaveFlag);
        self::assertTrue($model->beforeSaveFlag);

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

        self::assertEquals($expected, $model->getLastActivityLog()->getData());

        // Save without change
        $model->beforeSaveFlag = false;
        $model->afterSaveFlag = false;
        self::assertTrue($model->save());
        self::assertFalse($model->afterSaveFlag);
        self::assertFalse($model->beforeSaveFlag);
    }

    public function testEventSaveMessageCallback()
    {
        // Create
        $model = new User();
        $model->login = 'John';

        $expected = [
            'test' => 'test',
            'status' => [
                'new' => [
                    'id' => 10,
                    'value' => 'Active',
                ],
            ],
            'login' => [
                'new' => [
                    'value' => 'John',
                ],
            ],
            'is_hidden' => [
                'new' => [
                    'value' => false,
                ],
            ],
        ];

        /** @var ActiveLogBehavior $logger */
        $logger = $model->getBehavior('logger');
        $logger->beforeSaveMessage = function ($data) {
            return ['test' => 'test'] + $data;
        };

        self::assertTrue($model->save());

        $activityLog = $model->getLastActivityLog();
        self::assertNotNull($activityLog);
        self::assertEquals($expected, $activityLog->getData());

        // Update
        $model->login = 'buster2';

        $logger->beforeSaveMessage = function ($data) {
            return ['test' => 'test'] + $data + ['action' => 'Custom action'];
        };

        self::assertTrue($model->save());

        $expected = [
            'test' => 'test',
            'login' => [
                'old' => [
                    'value' => 'John'
                ],
                'new' => [
                    'value' => 'buster2'
                ],
            ],
            'action' => 'Custom action',
        ];

        self::assertEquals($expected, $model->getLastActivityLog()->getData());

        // Save without change
        self::assertTrue($model->save());
    }

    public function testArrayListValues()
    {
        // Create
        $model = new User();
        $model->arrayStatus = [
            User::STATUS_ACTIVE,
            User::STATUS_DRAFT,
        ];

        self::assertTrue($model->save(false));

        $statusList = $model->getStatusList();

        $expected = [
            'arrayStatus' => [
                'new' => [
                    'id' => [
                        User::STATUS_ACTIVE,
                        User::STATUS_DRAFT,
                    ],
                    'value' => [
                        User::STATUS_ACTIVE => $statusList[User::STATUS_ACTIVE],
                        User::STATUS_DRAFT => $statusList[User::STATUS_DRAFT],
                    ]
                ]
            ]
        ];

        self::assertEquals($expected, $model->getLastActivityLog()->getData());

        // Update
        $model->arrayStatus = [
            User::STATUS_ACTIVE,
        ];

        self::assertTrue($model->save(false));

        $expected = [
            'arrayStatus' => [
                'old' => [
                    'id' => [
                        User::STATUS_ACTIVE,
                        User::STATUS_DRAFT,
                    ],
                    'value' => [
                        User::STATUS_ACTIVE => $statusList[User::STATUS_ACTIVE],
                        User::STATUS_DRAFT => $statusList[User::STATUS_DRAFT],
                    ]
                ],
                'new' => [
                    'id' => [
                        User::STATUS_ACTIVE,
                    ],
                    'value' => [
                        User::STATUS_ACTIVE => $statusList[User::STATUS_ACTIVE],
                    ]
                ]
            ]
        ];

        self::assertEquals($expected, $model->getLastActivityLog()->getData());

        // Delete
        self::assertEquals(1, $model->delete());

        /** @var ActivityLog[]|\Countable $logModels */
        $logModels = ActivityLog::findAll([
            'entity_name' => $model->getEntityName(),
            'entity_id' => $model->getEntityId(),
        ]);

        $expected = [
            'arrayStatus' => [
                'old' => [
                    'id' => [
                        User::STATUS_ACTIVE,
                    ],
                    'value' => [
                        User::STATUS_ACTIVE => $statusList[User::STATUS_ACTIVE],
                    ]
                ],
                'new' => [
                    'id' => null,
                    'value' => null
                ]
            ]
        ];

        self::assertEquals($expected, $logModels[0]->getData());
    }
}