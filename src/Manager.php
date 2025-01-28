<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

use lav45\activityLogger\middlewares\Middleware;
use lav45\activityLogger\middlewares\MiddlewarePipeline;
use lav45\activityLogger\storage\DeleteCommand;
use lav45\activityLogger\storage\MessageData;
use lav45\activityLogger\storage\StorageInterface;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\di\Instance;

class Manager extends BaseObject implements ManagerInterface
{
    public bool $debug = YII_DEBUG;

    /** @var array{string, array, Middleware} */
    public array $middlewares = [];

    private StorageInterface $storage;

    public function __construct(
        StorageInterface $storage,
        array            $config = []
    )
    {
        parent::__construct($config);
        $this->storage = $storage;
    }

    /**
     * @return Middleware[]
     */
    private function createMiddlewares(): array
    {
        $result = [];
        foreach ($this->middlewares as $middleware) {
            $result[] = Instance::ensure($middleware, Middleware::class);
        }
        return $result;
    }

    public function createMessageBuilder(string $entityName): MessageBuilderInterface
    {
        $middlewares = $this->createMiddlewares();
        $pipeline = new MiddlewarePipeline(...$middlewares);
        $builder = new MessageBuilder($entityName);
        return $pipeline->handle($builder);
    }

    public function log(MessageData $message): bool
    {
        try {
            $this->storage->save($message);
            return true;
        } catch (Throwable $e) {
            $this->throwException($e);
            return false;
        }
    }

    public function delete(DeleteCommand $command): bool
    {
        try {
            $this->storage->delete($command);
            return true;
        } catch (Throwable $e) {
            $this->throwException($e);
            return false;
        }
    }

    private function throwException(Throwable $e): void
    {
        if ($this->debug) {
            throw $e;
        }
        Yii::error($e->getMessage(), static::class);
    }
}