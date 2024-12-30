<?php declare(strict_types=1);

namespace lav45\activityLogger\middlewares;

use Closure;
use lav45\activityLogger\MessageBuilderInterface;

final class MiddlewarePipeline
{
    private array $middlewares;

    public function __construct(Middleware ...$middleware)
    {
        $this->middlewares = $middleware;
    }

    public function handle(MessageBuilderInterface $builder): MessageBuilderInterface
    {
        $middlewareChain = array_reduce(
            array_reverse($this->middlewares),
            static function (Closure $next, Middleware $middleware) {
                return static fn (MessageBuilderInterface $builder): MessageBuilderInterface => $middleware->handle($builder, $next);
            },
            static function(MessageBuilderInterface $builder): MessageBuilderInterface {
                return $builder;
            }
        );
        return $middlewareChain($builder);
    }
}