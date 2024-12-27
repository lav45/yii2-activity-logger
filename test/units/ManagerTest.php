<?php

namespace lav45\activityLogger\test\units {

    use lav45\activityLogger\Manager;
    use lav45\activityLogger\storage\DeleteCommand;
    use lav45\activityLogger\storage\MessageData;
    use lav45\activityLogger\test\components\FakeStorage;
    use lav45\activityLogger\test\models\User;
    use PHPUnit\Framework\TestCase;
    use Yii;
    use yii\web\Application;
    use yii\web\IdentityInterface;
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

        private function createUser(): User
        {
            $user = new User();
            $user->login = 'buster';
            $user->save();
            return $user;
        }

        private function removeUser(): void
        {
            User::deleteAll();
        }

        private function loginUser(IdentityInterface $user): void
        {
            Yii::$app->getUser()->setIdentity($user);
        }

        private function logoutUser(): void
        {
            Yii::$app->getUser()->setIdentity(null);
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

            $user = $this->createUser();
            $this->loginUser($user);

            [$manager, $storage] = $this->createManager();
            $manager->userNameAttribute = 'login';

            $env = 'console test 1';
            $entityName = 'test';
            $data = ['test'];
            self::$time = time();

            $message = new MessageData([
                'entityName' => $entityName,
                'createdAt' => time(),
                'data' => $data,
                'env' => $env,
            ]);

            $manager->log($message);

            $storageMessage = $storage->message;

            $this->assertEquals($storageMessage->userId, $user->id);
            $this->assertEquals($storageMessage->userName, $user->login);
            $this->assertEquals($storageMessage->entityName, $entityName);
            $this->assertEquals($storageMessage->data, $data);
            $this->assertEquals($storageMessage->createdAt, self::$time);
            $this->assertEquals($storageMessage->env, $env);

            $this->removeUser();
            $this->logoutUser();
            Yii::$app = $oldApp;
            self::$time = null;
        }

        public function testLogWithOutUser(): void
        {
            [$manager, $storage] = $this->createManager();

            $env = 'console test 2';
            $entityName = 'test';
            $data = ['test'];
            self::$time = time();

            $message = new MessageData([
                'entityName' => $entityName,
                'createdAt' => time(),
                'data' => $data,
                'env' => $env,
            ]);

            $manager->log($message);

            $storageMessage = $storage->message;

            $this->assertNull($storageMessage->userId);
            $this->assertNull($storageMessage->userName);
            $this->assertEquals($storageMessage->entityName, $entityName);
            $this->assertEquals($storageMessage->data, $data);
            $this->assertEquals($storageMessage->createdAt, self::$time);
            $this->assertEquals($storageMessage->env, $env);

            self::$time = null;
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
    }
}

namespace lav45\activityLogger {

    use lav45\activityLogger\test\units\ManagerTest;

    /**
     * Mock for the time() function for web classes.
     * @return int
     */
    function time(): int
    {
        return ManagerTest::$time ?: \time();
    }
}