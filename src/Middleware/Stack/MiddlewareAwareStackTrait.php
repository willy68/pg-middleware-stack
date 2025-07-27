<?php

/**
 * https://github.com/thephpleague/route
 */

declare(strict_types=1);

namespace Pg\Middleware\Stack;

use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Container\ContainerInterface;

trait MiddlewareAwareStackTrait
{
    protected array $middlewares = [];

    /**
     * Add a middlewares array
     *
     * @param string[]|MiddlewareInterface[]|callable[] $middlewares
     * @return self
     */
    public function middlewares(array $middlewares): static
    {
        foreach ($middlewares as $middleware) {
            $this->middleware($middleware);
        }
        return $this;
    }

    /**
     * Add middleware
     *
     * @param callable|string|MiddlewareInterface $middleware
     * @return self
     */
    public function middleware(callable|MiddlewareInterface|string $middleware): static
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Add middleware in first
     *
     * @param callable|string|MiddlewareInterface $middleware
     * @return self
     */
    public function prependMiddleware(callable|MiddlewareInterface|string $middleware): static
    {
        array_unshift($this->middlewares, $middleware);
        return $this;
    }

    /**
     * Get first middleware from the stack
     *
     * @param ContainerInterface $c
     * @return MiddlewareInterface|Closure|callable|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function shiftMiddleware(ContainerInterface $c): null|MiddlewareInterface|Closure|callable
    {
        $middleware = array_shift($this->middlewares);
        if ($middleware === null) {
            return null;
        }

        if (is_string($middleware)) {
            if (!$c->has($middleware)) {
                return null;
            }
            $middleware = $c->get($middleware);
        }

        return $middleware;
    }

    /**
     * get middleware stack
     *
     * @return iterable
     */
    public function getMiddlewareStack(): iterable
    {
        return $this->middlewares;
    }
}
