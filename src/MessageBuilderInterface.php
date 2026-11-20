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
    public function withEntityId(string|int $id): self;

    public function withUserId(string $id): self;

    public function withUserName(string $name): self;

    public function withAction(?string $action): self;

    public function withEnv(string $env): self;

    public function withData(array|string $data): self;

    public function build(int $now): MessageData;
}