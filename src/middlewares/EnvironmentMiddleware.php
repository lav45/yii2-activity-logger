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

final class EnvironmentMiddleware implements Middleware
{
    private string $env;

    public function __construct(string $env)
    {
        $this->env = $env;
    }

    public function handle(MessageBuilderInterface $builder, Closure $next): MessageBuilderInterface
    {
        $builder = $builder->withEnv($this->env);
        return $next($builder);
    }
}