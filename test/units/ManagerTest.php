<?php

namespace lav45\activityLogger\test\units {

    use lav45\activityLogger\storage\DeleteCommand;
    use lav45\activityLogger\storage\MessageData;
    use lav45\activityLogger\Manager;
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

        private function createManager(): Manager
        {
            $manager = new Manager();
            $manager->storage = new FakeStorage();
            $manager->enabled = true;
            return $manager;
        }

        public function testDisabled(): void
        {
            $manager = $this->createManager();
            $manager->enabled = false;
            /** @var FakeStorage $storage */
            $storage = $manager->storage;

            $manager->log(new MessageData([
                'entityName' => 'test',
            ]));
            self::assertNull($storage->message);

            $manager->delete(new DeleteCommand([
                'entityName' => 'test',
            ]));
            self::assertNull($storage->command);
        }

        private $old_app;

        private function iniApplication(): void
        {
            $this->old_app = Yii::$app;

            Yii::$app = new Application([
                'id' => 'test',
                'basePath' => __DIR__,
                'components' => [
                    'cache' => $this->old_app->getCache(),
                    'db' => $this->old_app->getDb(),
                    'user' => [
                        'class' => WebUser::class,
                        'identityClass' => User::class,
                    ]
                ]
            ]);
        }

        private function resetApplication(): void
        {
            Yii::$app = $this->old_app;
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
            $this->iniApplication();
            $user = $this->createUser();
            $this->loginUser($user);

            $manager = $this->createManager();
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

            /** @var MessageData $storageMessage */
            $storageMessage = $manager->storage->message;

            self::assertEquals($storageMessage->userId, $user->id);
            self::assertEquals($storageMessage->userName, $user->login);
            self::assertEquals($storageMessage->entityName, $entityName);
            self::assertEquals($storageMessage->data, $data);
            self::assertEquals($storageMessage->createdAt, self::$time);
            self::assertEquals($storageMessage->env, $env);

            $this->removeUser();
            $this->logoutUser();
            $this->resetApplication();
            self::$time = null;
        }

        public function testLogWithOutUser(): void
        {
            $manager = $this->createManager();

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

            /** @var MessageData $storageMessage */
            $storageMessage = $manager->storage->message;

            self::assertNull($storageMessage->userId);
            self::assertNull($storageMessage->userName);
            self::assertEquals($storageMessage->entityName, $entityName);
            self::assertEquals($storageMessage->data, $data);
            self::assertEquals($storageMessage->createdAt, self::$time);
            self::assertEquals($storageMessage->env, $env);

            self::$time = null;
        }

        public function testDelete(): void
        {
            $manager = $this->createManager();

            $command = new DeleteCommand([
                'entityName' => 'entityName',
            ]);
            $manager->delete($command);

            /** @var FakeStorage $storage */
            $storage = $manager->storage;
            self::assertEquals($storage->command, $command);
            self::assertEquals($storage->command->entityName, 'entityName');
            self::assertNull($storage->command->oldThan);

            $command = new DeleteCommand([
                'oldThan' => time(),
            ]);
            $manager->delete($command);
            self::assertEquals($storage->command, $command);
            self::assertEquals($storage->command->oldThan, $command->oldThan);
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