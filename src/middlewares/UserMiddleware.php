<?php
/**
 * @link https://github.com/lav45/yii2-activity-logger
 * @copyright Copyright (c) 2017 LAV45
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\activityLogger\middlewares;

use Closure;
use lav45\activityLogger\MessageBuilderInterface;

final class UserMiddleware implements Middleware
{
    private UserInterface $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    public function handle(MessageBuilderInterface $builder, Closure $next): MessageBuilderInterface
    {
        $builder = $builder
            ->withUserId($this->user->getId())
            ->withUserName($this->user->getName());

        return $next($builder);
    }
}