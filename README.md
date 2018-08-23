# Stratigility universal module

This package integrates Stratigility in any [container-interop](https://github.com/container-interop/service-provider) compatible framework/container.

## Installation

```
composer require thecodingmachine/stratigility-harmony ^3
```

If your container supports autodiscovery by thecodingmachine/discovery, there is nothing more to do.
Otherwise, you need to register the [`TheCodingMachine\StratigilityServiceProvider`](src/StratigilityServiceProvider.php) and the `TheCodingMachine\MiddlewareListServiceProvider` into your container.

Refer to your framework or container's documentation to learn how to register *service providers*.

## Usage

This module registers those services in your container:

- A Zend RequestHandlerRunner under the `RequestHandlerRunner::class` key.
  Use the `run` method to answer calls:
  ```php
  $runner = $container->get(RequestHandlerRunner::class);
  $runner->run();
  ```

- A MiddlewarePipe instance under the `Zend\Stratigility\MiddlewarePipe` key (alias = `RequestHandlerInterface::class`).
  Use this middleware pipe to add your own middlewares:
  ```php
  $middlewarePipe = $container->get(MiddlewarePipe::class);
  $middlewarePipe->pipe($myMiddleware);
  ```
  
Several other instances are worth mentionning:

- `SapiStreamEmitter::class` (alias = `EmitterInterface::class`) is used to emit responses
- `serverRequestFactory`: a factory to create PSR-7 requests (used internally by `RequestHandlerRunner`)
- `serverRequestErrorResponseGenerator`: a factory to error response (used internally by `RequestHandlerRunner`)


## About the middlewares priority queue

Depending on the middleware you are registering, you generally have a fairly good idea of the order your middleware should run compared to other middlewares.
The Stratigility service provider will use the middleware list provided by [thecodingmachine/middleware-list-universal-module](https://github.com/thecodingmachine/middleware-list-universal-module).

Please have a look at this package to see how to add middlewares automatically.
