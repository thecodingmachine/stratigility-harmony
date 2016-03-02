# Stratigility universal module

This package integrates Stratigility in any [container-interop](https://github.com/container-interop/service-provider) compatible framework/container.

## Installation

```
composer require thecodingmachine/stratigility-harmony
```

If your container supports autodiscovery by Puli, there is nothing more to do.
Otherwise, you need to register the [`TheCodingMachine\StratigilityServiceProvider`](src/StratigilityServiceProvider.php) into your container.

Refer to your framework or container's documentation to learn how to register *service providers*.

## Usage

This module registers 3 services in your container:

- A Zend Diactoros Server under the `Zend\Diactoros\Server` key.
  Use the `listen` method to answer calls:
  ```php
  $server = $container->get(Server::class);
  $server->listen();
  ```

- A MiddlewarePipe instance under the `Zend\Stratigility\MiddlewarePipe` key.
  Use this middleware pipe to add your own middlewares:
  ```php
  $middlewarePipe = $container->get(MiddlewarePipe::class);
  $middlewarePipe->pipe($myMiddleware);
  ```

- Alternatively, if you want to register a middleware in an orderly fashion, you can use the middleware priority queue under the `StratigilityServiceProvider::MIDDLEWARES_QUEUE` key.


## About the middlewares priority queue

Depending on the middleware you are registering, you generally have a fairly good idea of the order it should run compared to other middlewares.

Let's split the middlewares in 4 families:

- **Utility middlewares**: those are typically handled at the beginning of a request. They are used to modify/enrich the response and pass it to other middlewares.
  In this category, you would put middlewares that compute the response time, middlewares that add geolocation information, middlewares that manage sessions, etc...
- **Routers**: those are middlewares that are typically used to handle a request and return a response. They respond on specific routes or pass the request along to the next router if they don't know that route.
- **Page not found routers**: those are middlewares in charge of answering a 404 answer if no middleware has handled the request. This is the last "classical" middleware of the queue.
- **Error handling middlewares**: Finally, at the very end of the queue, you will find the list of middlewares that handle errors and exceptions. They are in charge of logging or displaying error messages.

Based on those 4 families, the StratigilityServiceProvider provides a SPL Priority Queue that one can use to register any middleware at the right point in the queue.

The service provider defines 12 constants you can use to insert a middleware at a given point:

- `MiddlewareOrder::UTILITY_EARLY` (-50)
- `MiddlewareOrder::UTILITY` (0)
- `MiddlewareOrder::UTILITY_LATE` (50)
- `MiddlewareOrder::ROUTER_EARLY` (950)
- `MiddlewareOrder::ROUTER` (1000)
- `MiddlewareOrder::ROUTER_LATE` (1050)
- `MiddlewareOrder::PAGE_NOT_FOUND_EARLY` (1950)
- `MiddlewareOrder::PAGE_NOT_FOUND` (2000)
- `MiddlewareOrder::PAGE_NOT_FOUND_LATE` (2050)
- `MiddlewareOrder::EXCEPTION_EARLY` (2950)
- `MiddlewareOrder::EXCEPTION` (3000)
- `MiddlewareOrder::EXCEPTION_LATE` (3050)

Each "family" has 3 variants: EARLY, NORMAL and LATE, so you can add more fine grained tuning if you want a utility to be triggered before another one, etc...

So if you want to register a middleware, you would typically write:

```php
$middlewareQueue = $container->get(StratigilityServiceProvider::MIDDLEWARES_QUEUE);
/* @var $middlewareQueue \SplPriorityQueue */
$middlewareQueue->insert($myMiddleware, MiddlewareOrder::UTILITY);
```
