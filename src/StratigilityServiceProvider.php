<?php

namespace TheCodingMachine;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProviderInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Server;
use Zend\Stratigility\Middleware\NotFoundHandler;
use Zend\Stratigility\MiddlewarePipe;

class StratigilityServiceProvider implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            Server::class => [self::class, 'createServer'],
            MiddlewarePipe::class => [self::class, 'createMiddlewarePipe'],
        ];
    }

    public function getExtensions()
    {
        return [
            MiddlewareListServiceProvider::MIDDLEWARES_QUEUE => [self::class, 'registerPageNotFoundMiddleware']
        ];
    }

    public static function createServer(ContainerInterface $container) : Server
    {
        $server = Server::createServer($container->get(MiddlewarePipe::class), $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

        return $server;
    }

    /**
     * This factory creates a Stratigility MiddlewarePipe.
     * It also adds to the pipe any middlewares in the 'middlewaresQueue' service.
     *
     * @param ContainerInterface $container
     * @return MiddlewarePipe
     */
    public static function createMiddlewarePipe(ContainerInterface $container) : MiddlewarePipe
    {
        $app = new MiddlewarePipe();
        $app->setResponsePrototype(new Response());
        $middlewaresQueue = $container->get(MiddlewareListServiceProvider::MIDDLEWARES_QUEUE);

        /* @var $middlewaresQueue \SplPriorityQueue */
        foreach ($middlewaresQueue as $middleware) {
            $app->pipe($middleware);
        }

        return $app;
    }

    public static function registerPageNotFoundMiddleware(ContainerInterface $container, \SplPriorityQueue $queue): \SplPriorityQueue
    {
        $queue->insert(new NotFoundHandler(new Response()), MiddlewareOrder::PAGE_NOT_FOUND_LATE);
    }
}
