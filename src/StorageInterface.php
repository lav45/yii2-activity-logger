<?php
/**
 * @link https://github.com/LAV45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger;

/**
 * Interface StorageInterface
 * @package lav45\activityLogger
 */
interface StorageInterface
{
    /**
     * @param LogMessageDTO $message
     */
    public function save(LogMessageDTO $message);

    /**
     * @param LogMessageDTO $message
     * @param int|null $old_than
     */
    public function delete(LogMessageDTO $message, $old_than = null);
}
