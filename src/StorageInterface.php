<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Aleksey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

/**
 * Interface StorageInterface
 * @package lav45\activityLogger
 */
interface StorageInterface
{
    public function save(MessageData $message): void;

    public function delete(DeleteCommand $command): void;
}
