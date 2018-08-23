<?php

namespace TheCodingMachine;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TheCodingMachine\Funky\Annotations\Factory;
use TheCodingMachine\Funky\Annotations\Tag;
use TheCodingMachine\Funky\ServiceProvider;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;
use Zend\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use Zend\Stratigility\Middleware\ErrorResponseGenerator;
use Zend\Stratigility\Middleware\NotFoundHandler;
use Zend\Stratigility\MiddlewarePipe;

class StratigilityServiceProvider extends ServiceProvider
{
    /**
     * @Factory()
     */
    public static function createRequestHandlerRunner(
        RequestHandlerInterface $handler,
        EmitterInterface $emitter,
        ContainerInterface $container
    ): RequestHandlerRunner {
        return new RequestHandlerRunner(
            $handler,
            $emitter,
            $container->get('serverRequestFactory'),
            $container->get('serverRequestErrorResponseGenerator')
        );
    }

    /**
     * @Factory(aliases={EmitterInterface::class})
     */
    public static function createSapiStreamEmitter(): SapiStreamEmitter
    {
        return new SapiStreamEmitter();
    }

    /**
     * @Factory(aliases={RequestHandlerInterface::class})
     */
    public static function createRequestHandler(ContainerInterface $container): MiddlewarePipe
    {
        $queue = clone $container->get(MiddlewareListServiceProvider::MIDDLEWARES_QUEUE);
        $middlewarePipe = new MiddlewarePipe();
        foreach ($queue as $middleware) {
            $middlewarePipe->pipe($middleware);
        }
        return $middlewarePipe;
    }

    /**
     * @Factory(name="serverRequestFactory")
     */
    public static function createServerRequestFactory(): callable
    {
        return [ServerRequestFactory::class, 'fromGlobals'];
    }

    /**
     * @Factory(name="serverRequestErrorResponseGenerator")
     */
    public static function createServerRequestErrorResponseGenerator(): callable
    {
        return function (\Throwable $e) {
            $generator = new ErrorResponseGenerator();
            return $generator($e, new ServerRequest(), new Response());
        };
    }

    /**
     * @Factory(tags={@Tag(name=MiddlewareListServiceProvider::MIDDLEWARES_QUEUE, priority=MiddlewareOrder::PAGE_NOT_FOUND_LATE)})
     */
    public static function registerPageNotFoundMiddleware(): NotFoundHandler
    {
        return new NotFoundHandler(function () {
            return new Response();
        });
    }
}
