# pg-middleware-stack

Gestion de pile de middlewares PSR-15 pour PHP.

## Installation

Utilisez Composer pour installer le package :

```bash
composer require votre-vendor/pg-middleware-stack
```

## Utilisation

Incluez le trait `MiddlewareAwareStackTrait` dans votre classe pour gérer une pile de middlewares :

```php
use Pg\Middleware\Stack\MiddlewareAwareStackTrait;

class MyMiddlewareStack
{
    use MiddlewareAwareStackTrait;
}
```

### Ajouter des middlewares

```php
$stack = new MyMiddlewareStack();
$stack->middleware($monMiddleware);
$stack->middlewares([$middleware1, $middleware2]);
$stack->prependMiddleware($middlewarePrioritaire);
```

### Récupérer et exécuter un middleware

```php
$middleware = $stack->shiftMiddleware($container);
if ($middleware) {
    // Exécuter le middleware
}
```

## API

- `middleware($middleware)` : Ajoute un middleware à la fin de la pile.
- `middlewares(array $middlewares)` : Ajoute plusieurs middlewares.
- `prependMiddleware($middleware)` : Ajoute un middleware au début de la pile.
- `shiftMiddleware(ContainerInterface $c)` : Retire et retourne le premier middleware.
- `getMiddlewareStack()` : Retourne la pile actuelle.

## Licence

MIT
