<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

use lav45\activityLogger\storage\MessageData;

interface MessageBuilderInterface
{
    /**
     * @param string|int $id
     */
    public function withEntityId($id): self;

    public function withUserId(string $id): self;

    public function withUserName(string $name): self;

    public function withAction(string|null $action): self;

    public function withEnv(string $env): self;

    /**
     * @param array|string $data
     */
    public function withData($data): self;

    public function build(int $now): MessageData;
}