<?php

declare(strict_types=1);

namespace Pg\Tests\Middleware\Stack;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MiddlewareAwareStackTraitTest extends TestCase
{
    private MiddlewareAwareStack $stack;
    private ContainerInterface $container;

    public function testInitialStackIsEmpty(): void
    {
        $this->assertCount(0, $this->stack->getMiddlewareStack());
    }

    public function testAddSingleMiddleware(): void
    {
        $middleware = new TestMiddleware1();
        $this->stack->middleware($middleware);

        $stack = $this->stack->getMiddlewareStack();
        $this->assertCount(1, $stack);
        $this->assertSame($middleware, $stack[0]);
    }

    public function testAddMultipleMiddlewares(): void
    {
        $middleware1 = new TestMiddleware1();
        $middleware2 = new TestMiddleware2();

        $this->stack->middlewares([$middleware1, $middleware2]);

        $stack = $this->stack->getMiddlewareStack();
        $this->assertCount(2, $stack);
        $this->assertSame($middleware1, $stack[0]);
        $this->assertSame($middleware2, $stack[1]);
    }

    public function testPrependMiddleware(): void
    {
        $middleware1 = new TestMiddleware1();
        $middleware2 = new TestMiddleware2();

        $this->stack->middleware($middleware1);
        $this->stack->prependMiddleware($middleware2);

        $stack = $this->stack->getMiddlewareStack();
        $this->assertSame($middleware2, $stack[0]);
        $this->assertSame($middleware1, $stack[1]);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testShiftMiddlewareWithObject(): void
    {
        $middleware = new TestMiddleware1();
        $this->stack->middleware($middleware);

        $shifted = $this->stack->shiftMiddleware($this->container);
        $this->assertSame($middleware, $shifted);
        $this->assertCount(0, $this->stack->getMiddlewareStack());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testShiftMiddlewareWithString(): void
    {
        $middleware = new TestMiddleware1();

        $this->container->method('has')
            ->with('TestMiddleware1')
            ->willReturn(true);

        $this->container->method('get')
            ->with('TestMiddleware1')
            ->willReturn($middleware);

        $this->stack->middleware('TestMiddleware1');
        $shifted = $this->stack->shiftMiddleware($this->container);

        $this->assertSame($middleware, $shifted);
        $this->assertCount(0, $this->stack->getMiddlewareStack());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testShiftMiddlewareWithNonExistentService(): void
    {
        $this->container->method('has')
            ->with('NonExistent')
            ->willReturn(false);

        $this->stack->middleware('NonExistent');
        $shifted = $this->stack->shiftMiddleware($this->container);

        $this->assertNull($shifted);
        $this->assertCount(0, $this->stack->getMiddlewareStack());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testShiftMiddlewareWithEmptyStack(): void
    {
        $shifted = $this->stack->shiftMiddleware($this->container);
        $this->assertNull($shifted);
    }

    public function testGetMiddlewareStack(): void
    {
        $middleware1 = new TestMiddleware1();
        $middleware2 = new TestMiddleware2();

        $this->stack->middleware($middleware1);
        $this->stack->middleware($middleware2);

        $stack = $this->stack->getMiddlewareStack();
        $this->assertCount(2, $stack);
        $this->assertSame($middleware1, $stack[0]);
        $this->assertSame($middleware2, $stack[1]);
    }

    public function testMiddlewareStackIsIterable(): void
    {
        $middleware = new TestMiddleware1();
        $this->stack->middleware($middleware);

        $count = 0;
        foreach ($this->stack as $item) {
            $this->assertSame($middleware, $item);
            $count++;
        }

        $this->assertEquals(1, $count);
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->stack = new MiddlewareAwareStack();

        // Create a mock container for testing
        $this->container = $this->createMock(ContainerInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
    }
}
