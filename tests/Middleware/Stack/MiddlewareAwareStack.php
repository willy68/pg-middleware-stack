<?php

declare(strict_types=1);

namespace Pg\Tests\Middleware\Stack;

// Test class that uses the trait
use Pg\Middleware\Stack\MiddlewareAwareStackTrait;

class MiddlewareAwareStack implements \IteratorAggregate
{
    use MiddlewareAwareStackTrait;

    public function getIterator(): \Traversable
    {
        yield from $this->getMiddlewareStack();
    }
}
