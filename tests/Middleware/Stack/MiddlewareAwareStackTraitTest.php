<?php

declare(strict_types=1);

namespace Pg\Tests\Middleware\Stack;

use Pg\Middleware\Stack\MiddlewareAwareStackTrait;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareAwareStackTraitTest extends TestCase
{
    private object $middlewareAware;

    public function testAddSingleMiddleware()
    {
        $middleware = $this->getMockMiddleware('test');
        $result = $this->middlewareAware->middleware($middleware);

        $this->assertSame($this->middlewareAware, $result);
        $this->assertCount(1, $this->middlewareAware->getMiddlewareStack());
        $this->assertSame($middleware, $this->middlewareAware->getMiddlewareStack()[0]);
    }

    protected function getMockMiddleware(string $name): object
    {
        return new class ($name) implements MiddlewareInterface {
            private string $name;

            public function __construct(string $name = '')
            {
                $this->name = $name;
            }

            public function getName(): string
            {
                return $this->name;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $handler->handle($request);
            }
        };
    }

    public function testAddMultipleMiddlewares()
    {
        $middleware1 = $this->getMockMiddleware('one');
        $middleware2 = $this->getMockMiddleware('two');

        $result = $this->middlewareAware->middlewares([$middleware1, $middleware2]);

        $this->assertSame($this->middlewareAware, $result);
        $this->assertCount(2, $this->middlewareAware->getMiddlewareStack());
        $this->assertSame($middleware1, $this->middlewareAware->getMiddlewareStack()[0]);
        $this->assertSame($middleware2, $this->middlewareAware->getMiddlewareStack()[1]);
    }

    public function testPrependMiddleware()
    {
        $middleware1 = $this->getMockMiddleware('one');
        $middleware2 = $this->getMockMiddleware('two');

        $this->middlewareAware->middleware($middleware1);
        $result = $this->middlewareAware->prependMiddleware($middleware2);

        $this->assertSame($this->middlewareAware, $result);
        $this->assertCount(2, $this->middlewareAware->getMiddlewareStack());
        $this->assertSame($middleware2, $this->middlewareAware->getMiddlewareStack()[0]);
        $this->assertSame($middleware1, $this->middlewareAware->getMiddlewareStack()[1]);
    }

    /**
     * @throws Exception
     */
    public function testShiftMiddlewareWithObject()
    {
        $container = $this->createMock(ContainerInterface::class);
        $middleware = $this->getMockMiddleware('test');

        $this->middlewareAware->middleware($middleware);
        $result = $this->middlewareAware->shiftMiddleware($container);

        $this->assertSame($middleware, $result);
        $this->assertCount(0, $this->middlewareAware->getMiddlewareStack());
    }

    /**
     * @throws Exception
     */
    public function testShiftMiddlewareWithString()
    {
        $middleware = $this->getMockMiddleware('test');
        $container = $this->createMock(ContainerInterface::class);

        $container->method('has')
            ->with('middleware.service')
            ->willReturn(true);

        $container->method('get')
            ->with('middleware.service')
            ->willReturn($middleware);

        $this->middlewareAware->middleware('middleware.service');
        $result = $this->middlewareAware->shiftMiddleware($container);

        $this->assertSame($middleware, $result);
        $this->assertCount(0, $this->middlewareAware->getMiddlewareStack());
    }

    /**
     * @throws Exception
     */
    public function testShiftMiddlewareWithEmptyStack()
    {
        $container = $this->createMock(ContainerInterface::class);
        $result = $this->middlewareAware->shiftMiddleware($container);

        $this->assertNull($result);
    }

    protected function setUp(): void
    {
        $this->middlewareAware = $this->getMiddlewareStackClass();
    }

    protected function getMiddlewareStackClass(): object
    {
        return new class {
            use MiddlewareAwareStackTrait;
        };
    }
}
