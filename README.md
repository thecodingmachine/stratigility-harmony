# Stratigility universal module

This package integrates Stratigility in any [container-interop](https://github.com/container-interop/service-provider) compatible framework/container.

## Installation

```
composer require thecodingmachine/stratigility-harmony
```

If your container supports autodiscovery by Puli, there is nothing more to do.
Otherwise, you need to register the [`TheCodingMachine\StratigilityServiceProvider`](src/StratigilityServiceProvider.php) and the `TheCodingMachine\MiddlewareListServiceProvider` into your container.

Refer to your framework or container's documentation to learn how to register *service providers*.

## Usage

This module registers 2 services in your container:

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


## About the middlewares priority queue

Depending on the middleware you are registering, you generally have a fairly good idea of the order your middleware should run compared to other middlewares.
The Stratigility service provider will use the middleware list provided by [thecodingmachine/middleware-list-universal-module](https://github.com/thecodingmachine/middleware-list-universal-module).

Please have a look at this package to see how to add middlewares automatically.
