<?php

namespace lav45\activityLogger\test\units {

    use lav45\activityLogger\LogMessageDTO;
    use lav45\activityLogger\Manager;
    use lav45\activityLogger\test\components\FakeStorage;
    use lav45\activityLogger\test\models\User;
    use PHPUnit\Framework\TestCase;
    use Yii;
    use yii\web\Application;
    use yii\web\IdentityInterface;
    use yii\web\User as WebUser;

    /**
     * Class ManagerTest
     * @package lav45\activityLogger\test\units
     */
    class ManagerTest extends TestCase
    {
        /**
         * @var int virtual time to be returned by mocked time() function.
         * Null means normal time() behavior.
         */
        public static $time;

        /**
         * @return Manager
         */
        private function createManager()
        {
            $manager = new Manager();
            $manager->storage = new FakeStorage();
            $manager->enabled = true;
            return $manager;
        }

        public function testDisabled()
        {
            $manager = $this->createManager();
            $manager->enabled = false;
            /** @var FakeStorage $storage */
            $storage = $manager->storage;

            $message = new LogMessageDTO([
                'entityName' => 'test',
            ]);

            $manager->log($message);
            self::assertNull($storage->message);

            $manager->delete($message);
            self::assertNull($storage->message);
        }

        private $old_app;

        private function iniApplication()
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

        private function resetApplication()
        {
            Yii::$app = $this->old_app;
        }

        private function createUser()
        {
            $user = new User();
            $user->login = 'buster';
            $user->save();
            return $user;
        }

        private function removeUser()
        {
            User::deleteAll();
        }

        private function loginUser(IdentityInterface $user)
        {
            Yii::$app->getUser()->setIdentity($user);
        }

        private function logoutUser()
        {
            Yii::$app->getUser()->setIdentity(null);
        }

        public function testLogWithUser()
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

            $message = new LogMessageDTO([
                'entityName' => $entityName,
                'data' => $data,
                'env' => $env,
            ]);

            $manager->log($message);

            /** @var LogMessageDTO $storageMessage */
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

        public function testLogWithOutUser()
        {
            $manager = $this->createManager();

            $env = 'console test 2';
            $entityName = 'test';
            $data = ['test'];
            self::$time = time();

            $message = new LogMessageDTO([
                'entityName' => $entityName,
                'data' => $data,
                'env' => $env,
            ]);

            $manager->log($message);

            /** @var LogMessageDTO $storageMessage */
            $storageMessage = $manager->storage->message;

            self::assertNull($storageMessage->userId);
            self::assertNull($storageMessage->userName);
            self::assertEquals($storageMessage->entityName, $entityName);
            self::assertEquals($storageMessage->data, $data);
            self::assertEquals($storageMessage->createdAt, self::$time);
            self::assertEquals($storageMessage->env, $env);

            self::$time = null;
        }

        public function testDelete()
        {
            $manager = $this->createManager();

            $message = new LogMessageDTO([
                'entityName' => 'entityName',
            ]);

            $manager->delete($message);

            /** @var FakeStorage $storage */
            $storage = $manager->storage;
            self::assertEquals($storage->message, $message);
            self::assertNull($storage->old_than);

            $time = time();
            $manager->delete($message, $time);
            self::assertEquals($storage->message, $message);
            self::assertEquals($storage->old_than, $time);
        }
    }
}

namespace lav45\activityLogger {

    use lav45\activityLogger\test\units\ManagerTest;

    /**
     * Mock for the time() function for web classes.
     * @return int
     */
    function time()
    {
        return ManagerTest::$time ?: \time();
    }
}