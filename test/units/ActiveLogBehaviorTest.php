<?php

namespace lav45\activityLogger\test\units;

use lav45\activityLogger\ActiveLogBehavior;
use lav45\activityLogger\Manager;
use lav45\activityLogger\ManagerInterface;
use lav45\activityLogger\MessageEvent;
use lav45\activityLogger\middlewares\EnvironmentMiddleware;
use lav45\activityLogger\middlewares\UserInterface;
use lav45\activityLogger\middlewares\UserMiddleware;
use lav45\activityLogger\module\models\ActivityLog;
use lav45\activityLogger\storage\ArrayStorage;
use lav45\activityLogger\storage\DbStorage;
use lav45\activityLogger\storage\StorageInterface;
use lav45\activityLogger\test\models\LogUser as User;
use lav45\activityLogger\test\models\TestEntityName;
use lav45\activityLogger\test\models\UserEventMethod;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Event;
use yii\base\InvalidValueException;

class ActiveLogBehaviorTest extends TestCase
{
    protected function setUp(): void
    {
        $user = new class implements UserInterface {
            public function getId(): string
            {
                return '1';
            }

            public function getName(): string
            {
                return 'user';
            }
        };

        Yii::$container->setDefinitions([
            ManagerInterface::class => static fn() => Yii::$app->get('activityLogger'),
            StorageInterface::class => static fn() => Yii::$app->get('activityLoggerStorage'),
            UserInterface::class => $user,
        ]);
        Yii::$app->set('activityLogger', [
            '__class' => Manager::class,
            'middlewares' => [
                UserMiddleware::class,
                [
                    'class' => EnvironmentMiddleware::class,
                    '__construct()' => ['env' => 'test'],
                ],
            ]
        ]);
        Yii::$app->set('activityLoggerStorage', [
            '__class' => DbStorage::class,
        ]);
    }

    /**
     * @return User
     */
    private function createModel(): User
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

    protected function tearDown(): void
    {
        User::deleteAll();
        ActivityLog::deleteAll();

        Yii::$app->clear('activityLogger');
        Yii::$app->clear('activityLoggerStorage');

        Yii::$container->clear(ManagerInterface::class);
        Yii::$container->clear(StorageInterface::class);
        Yii::$container->clear(UserInterface::class);
    }

    public function testCreateModelWithDefaultOptions(): void
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

        Yii::$app->set('activityLogger', [
            '__class' => Manager::class,
            'middlewares' => [
                new UserMiddleware($user),
                new EnvironmentMiddleware($ent),
            ]
        ]);

        $model = new User();
        $this->assertTrue($model->save());

        $expected = [
            'status' => [
                'old' => [
                    'id' => null,
                    'value' => null,
                ],
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
            'is_hidden' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => false
                ]
            ]
        ];

        $activityLog = $model->getLastActivityLog();

        $this->assertEquals($expected, $activityLog->getData());
        $this->assertEquals($ent, $activityLog->env);
        $this->assertEquals($user->getId(), $activityLog->user_id);
        $this->assertEquals($user->getName(), $activityLog->user_name);
        $this->assertEquals('created', $activityLog->action);
    }

    public function testLogUnauthorizedUser(): void
    {
        Yii::$app->set('activityLogger', [
            '__class' => Manager::class,
            'middlewares' => [
                new UserMiddleware(),
            ]
        ]);

        $model = new User();
        $this->assertTrue($model->save());

        $expected = [
            'status' => [
                'old' => [
                    'id' => null,
                    'value' => null,
                ],
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
            'is_hidden' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => false
                ]
            ]
        ];

        $activityLog = $model->getLastActivityLog();

        $this->assertEquals($expected, $activityLog->getData());
        $this->assertEquals('created', $activityLog->action);
        $this->assertNull($activityLog->env);
        $this->assertNull($activityLog->user_id);
        $this->assertNull($activityLog->user_name);
    }

    public function testIsEmpty(): void
    {
        $model = new User();
        /** @var ActiveLogBehavior $logger */
        $logger = $model->getBehavior('logger');
        $logger->isEmpty = static function ($value) {
            return empty($value);
        };

        $this->assertTrue($model->save());

        $expected = [
            'status' => [
                'old' => [
                    'id' => null,
                    'value' => null,
                ],
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
        ];

        $this->assertEquals($expected, $model->getLastActivityLog()->getData());
    }

    public function testCreateModelWithCustomOptions(): void
    {
        $model = $this->createModel();
        $logData = $model->getLastActivityLog();

        $this->assertTrue($logData->created_at > 0);
        $this->assertEquals('created', $logData->action);
        $this->assertEquals('user', $logData->entity_name);
        $this->assertEquals($model->getPrimaryKey(), $logData->entity_id);

        $expected = [
            'status' => [
                'old' => [
                    'id' => null,
                    'value' => null,
                ],
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
            'login' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => 'buster'
                ]
            ],
            'is_hidden' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => false
                ]
            ],
            'friend_count' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => 5
                ]
            ],
            'salary' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => 100.5
                ]
            ],
            'birthday' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => '01.01.2005'
                ]
            ],
            'company_id' => [
                'old' => [
                    'id' => null,
                    'value' => null,
                ],
                'new' => [
                    'value' => 'Asus',
                    'id' => 1
                ]
            ]
        ];

        $this->assertEquals($expected, $logData->getData());
    }

    public function testSaveWithoutUpdateAttributes(): void
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
    public function testUpdateModel(array $values, array $expected): void
    {
        $model = $this->createModel();
        $model->setAttributes($values);
        $this->assertTrue($model->save());
        $logModel = $model->getLastActivityLog();
        $this->assertEquals('updated', $logModel->action);
        $this->assertEquals($expected, $logModel->getData());
    }

    public function updateModelDataProvider(): array
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

    public function testDeleteModel(): void
    {
        $model = $this->createModel();
        $this->assertEquals(1, $model->delete());

        $this->assertNotNull($model->company);

        /** @var array $logModels */
        $logModels = ActivityLog::findAll([
            'entity_name' => $model->getEntityName(),
            'entity_id' => $model->getEntityId(),
        ]);

        $this->assertCount(1, $logModels);

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
        $this->assertEquals('deleted', $logModels[0]->action);
    }

    public function testSoftDelete(): void
    {
        $model = $this->createModel();
        /** @var ActiveLogBehavior $logger */
        $logger = $model->getBehavior('logger');
        $logger->softDelete = true;

        $this->assertEquals(1, $model->delete());

        /** @var array $logModels */
        $logModels = ActivityLog::find()
            ->where([
                'entity_name' => $model->getEntityName(),
                'entity_id' => $model->getEntityId(),
            ])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $this->assertCount(2, $logModels);

        $expected = [
            'status' => [
                'old' => [
                    'id' => null,
                    'value' => null,
                ],
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
            'company_id' => [
                'old' => [
                    'id' => null,
                    'value' => null,
                ],
                'new' => [
                    'id' => 1,
                    'value' => 'Asus'
                ]
            ],
            'login' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => 'buster'
                ]
            ],
            'is_hidden' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => false
                ]
            ],
            'friend_count' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => 5
                ]
            ],
            'salary' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => 100.5
                ]
            ],
            'birthday' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => '01.01.2005'
                ]
            ]
        ];
        $this->assertEquals($expected, $logModels[0]->getData());
        $this->assertEquals('created', $logModels[0]->action);

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
        $this->assertEquals('deleted', $logModels[1]->action);
    }

    public function testLogEmptyAttributeAfterDeleteModel(): void
    {
        $model = new User();
        $model->birthday = '01.01.2005';
        $this->assertTrue($model->save());

        $expected = [
            'birthday' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => '01.01.2005'
                ]
            ],
            'status' => [
                'old' => [
                    'id' => null,
                    'value' => null,
                ],
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
            'is_hidden' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => false
                ]
            ],
        ];

        $this->assertEquals($expected, $model->getLastActivityLog()->getData());

        $this->assertEquals(1, $model->delete());

        /** @var ActivityLog[]|\Countable $logModels */
        $logModels = ActivityLog::findAll([
            'entity_name' => $model->getEntityName(),
            'entity_id' => $model->getEntityId(),
        ]);

        $this->assertCount(1, $logModels);

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

    public function testGetEntityName(): void
    {
        $model = $this->createModel();
        /** @var ActiveLogBehavior $logger */
        $logger = $model->getBehavior('logger');

        $this->assertEquals('user', $logger->getEntityName());

        $new_entity_name = 'custom entity name';
        $logger->getEntityName = static function () use ($new_entity_name) {
            return $new_entity_name;
        };

        $this->assertEquals($new_entity_name, $logger->getEntityName());

        $testModel = new TestEntityName();
        $this->assertEquals('test_entity_name', $testModel->getEntityName());
    }

    public function testDefaultGetEntityId(): void
    {
        $model = $this->createModel();
        /** @var ActiveLogBehavior $logger */
        $logger = $model->getBehavior('logger');

        $this->assertEquals($model->getPrimaryKey(), $logger->getEntityId());

        $logger->getEntityId = 123;
        $this->assertEquals(123, $logger->getEntityId());
    }

    public function testExceptionGetEntityId(): void
    {
        $this->expectException(InvalidValueException::class);
        $testModel = new TestEntityName();
        $testModel->getEntityId();
    }

    /**
     * @dataProvider customGetEntityIdDataProvider
     * @param string|int|array $custom_entity_id
     * @param string|int $result_entity_id
     */
    public function testCustomGetEntityId($custom_entity_id, $result_entity_id): void
    {
        $model = $this->createModel();
        /** @var ActiveLogBehavior $logger */
        $logger = $model->getBehavior('logger');

        $logger->getEntityId = static function () use ($custom_entity_id) {
            return $custom_entity_id;
        };

        $this->assertEquals($result_entity_id, $logger->getEntityId());
    }

    public function customGetEntityIdDataProvider(): array
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

    public function testDisabledLoggerBeforeStart(): void
    {
        Yii::$app->set('activityLoggerStorage', ArrayStorage::class);

        $model = $this->createModel();
        $model->setAttributes([
            'company_id' => 2,
            'salary' => 150.3
        ]);
        $this->assertTrue($model->save());
        $this->assertNull($model->getLastActivityLog());
    }

    public function testEventSaveMessage(): void
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
                $this->assertInstanceOf(Event::class, $event);
                $afterSaveFlag = true;
            });

        $beforeSaveFlag = false;
        $afterSaveFlag = false;
        $this->assertTrue($model->save());
        $this->assertTrue($afterSaveFlag);
        $this->assertTrue($beforeSaveFlag);

        $expected = [
            'info' => 'Custom info',
            'status' => [
                'old' => [
                    'id' => null,
                    'value' => null,
                ],
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
            'is_hidden' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => false
                ]
            ],
            'login' => [
                'old' => [
                    'value' => null,
                ],
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

        $this->assertEquals($expected, $model->getLastActivityLog()->getData());

        // Save without change
        $beforeSaveFlag = false;
        $afterSaveFlag = false;
        $this->assertTrue($model->save());
        $this->assertFalse($afterSaveFlag);
        $this->assertFalse($beforeSaveFlag);
    }

    public function testEventSaveMessageMethod(): void
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
                'old' => [
                    'id' => null,
                    'value' => null,
                ],
                'new' => [
                    'id' => 10,
                    'value' => 'Active'
                ]
            ],
            'is_hidden' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => false
                ]
            ],
            'login' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => 'buster'
                ]
            ],
            'event' => 'save message',
        ];

        $activityLog = $model->getLastActivityLog();

        $this->assertNotNull($activityLog);
        $this->assertEquals($expected, $activityLog->getData());

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

    public function testEventSaveMessageCallback(): void
    {
        // Create
        $model = new User();
        $model->login = 'John';

        $expected = [
            'test' => 'test',
            'status' => [
                'old' => [
                    'id' => null,
                    'value' => null,
                ],
                'new' => [
                    'id' => 10,
                    'value' => 'Active',
                ],
            ],
            'login' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => 'John',
                ],
            ],
            'is_hidden' => [
                'old' => [
                    'value' => null,
                ],
                'new' => [
                    'value' => false,
                ],
            ],
        ];

        /** @var ActiveLogBehavior $logger */
        $logger = $model->getBehavior('logger');
        $logger->beforeSaveMessage = static function ($data) {
            return ['test' => 'test'] + $data;
        };

        $this->assertTrue($model->save());

        $activityLog = $model->getLastActivityLog();
        $this->assertNotNull($activityLog);
        $this->assertEquals($expected, $activityLog->getData());

        // Update
        $model->login = 'buster2';

        $logger->beforeSaveMessage = static function ($data) {
            return ['test' => 'test'] + $data + ['action' => 'Custom action'];
        };

        $this->assertTrue($model->save());

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

        $this->assertEquals($expected, $model->getLastActivityLog()->getData());

        // Save without change
        $this->assertTrue($model->save());
    }

    public function testArrayListValues(): void
    {
        // Create
        $model = new User();
        $model->arrayStatus = [
            User::STATUS_ACTIVE,
            User::STATUS_DRAFT,
        ];

        $this->assertTrue($model->save(false));

        $statusList = $model->getStatusList();

        $expected = [
            'arrayStatus' => [
                'old' => [
                    'id' => null,
                    'value' => null,
                ],
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

        $this->assertEquals($expected, $model->getLastActivityLog()->getData());

        // Update
        $model->arrayStatus = [
            User::STATUS_ACTIVE,
        ];

        $this->assertTrue($model->save(false));

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

        $this->assertEquals($expected, $model->getLastActivityLog()->getData());

        // Delete
        $this->assertEquals(1, $model->delete());

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

        $this->assertEquals($expected, $logModels[0]->getData());
    }

    public function testFailRelation(): void
    {
        $model = new User();
        $model->fail_relation = 1;

        try {
            $model->save(false);
            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }
        $this->assertFalse($result);
    }

    public function testFailLink(): void
    {
        $model = new User();
        $model->fail_link = 1;

        try {
            $model->save(false);
            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }
        $this->assertFalse($result);
    }
}