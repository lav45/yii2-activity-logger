<?php

use lav45\activityLogger\Manager;
use lav45\activityLogger\middlewares\EnvironmentMiddleware;
use lav45\activityLogger\middlewares\UserInterface;
use lav45\activityLogger\middlewares\UserMiddleware;
use lav45\activityLogger\storage\DeleteCommand;
use lav45\activityLogger\storage\MessageData;
use lav45\activityLogger\test\components\ExceptionStorage;
use lav45\activityLogger\test\components\FakeStorage;
use lav45\activityLogger\test\models\User;
use PHPUnit\Framework\TestCase;
use yii\web\Application;
use yii\web\User as WebUser;

class ManagerTest extends TestCase
{
    /**
     * @var int|null virtual time to be returned by mocked time() function.
     * Null means normal time() behavior.
     */
    public static ?int $time = null;

    /**
     * @return array{Manager, FakeStorage}
     */
    private function createManager(): array
    {
        $storage = new FakeStorage();
        $manager = new Manager($storage);
        return [$manager, $storage];
    }

    public function testLogWithUser(): void
    {
        $oldApp = clone Yii::$app;
        Yii::$app = new Application([
            'id' => 'test',
            'basePath' => __DIR__,
            'components' => [
                'cache' => $oldApp->getCache(),
                'db' => $oldApp->getDb(),
                'user' => [
                    '__class' => WebUser::class,
                    'identityClass' => User::class,
                ]
            ]
        ]);

        Yii::$container->set(UserInterface::class, static fn() => Yii::$app->getUser()->getIdentity());

        $user = new User();
        $user->login = 'buster';
        $user->save();

        Yii::$app->getUser()->setIdentity($user);

        [$manager, $storage] = $this->createManager();

        $env = 'console test 1';
        $entityName = 'test';
        $data = ['test'];
        $time = time();

        $manager->middlewares = [
            new EnvironmentMiddleware($env),
            new UserMiddleware($user),
        ];

        $message = $manager->createMessageBuilder($entityName)
            ->withData($data)
            ->build($time);

        $manager->log($message);

        $storageMessage = $storage->message;

        $this->assertEquals($storageMessage->userId, $user->id);
        $this->assertEquals($storageMessage->userName, $user->login);
        $this->assertEquals($storageMessage->entityName, $entityName);
        $this->assertEquals($storageMessage->data, $data);
        $this->assertEquals($storageMessage->createdAt, $time);
        $this->assertEquals($storageMessage->env, $env);

        User::deleteAll();

        Yii::$app = $oldApp;
        Yii::$container->clear(UserInterface::class);
    }

    public function testLogWithOutUser(): void
    {
        [$manager, $storage] = $this->createManager();

        $env = 'console test 2';
        $entityName = 'test';
        $data = ['test'];
        $time = time();

        $manager->middlewares = [
            new EnvironmentMiddleware($env),
        ];

        $message = $manager->createMessageBuilder($entityName)
            ->withData($data)
            ->build($time);

        $manager->log($message);

        $storageMessage = $storage->message;

        $this->assertNull($storageMessage->userId);
        $this->assertNull($storageMessage->userName);
        $this->assertEquals($storageMessage->entityName, $entityName);
        $this->assertEquals($storageMessage->data, $data);
        $this->assertEquals($storageMessage->createdAt, $time);
        $this->assertEquals($storageMessage->env, $env);
    }

    public function testDelete(): void
    {
        [$manager, $storage] = $this->createManager();

        $command = new DeleteCommand([
            'entityName' => 'entityName',
        ]);
        $manager->delete($command);

        $this->assertEquals($storage->command, $command);
        $this->assertEquals($storage->command->entityName, 'entityName');
        $this->assertNull($storage->command->oldThan);

        $command = new DeleteCommand([
            'oldThan' => time(),
        ]);
        $manager->delete($command);
        $this->assertEquals($storage->command, $command);
        $this->assertEquals($storage->command->oldThan, $command->oldThan);
    }

    public function testException(): void
    {
        $storage = new ExceptionStorage();
        $manager = new Manager($storage);

        $manager->debug = false;
        $this->assertFalse($manager->log(new MessageData()));
        $this->assertFalse($manager->delete(new DeleteCommand()));

        $manager->debug = true;

        try {
            $manager->log(new MessageData());
            $result = false;
        } catch (\Exception $e) {
            $result = true;
        }
        $this->assertTrue($result);

        try {
            $manager->delete(new DeleteCommand());
            $result = false;
        } catch (\Exception $e) {
            $result = true;
        }
        $this->assertTrue($result);
    }
}