<?php

namespace TheCodingMachine;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Zend\Diactoros\Response;
use Zend\Diactoros\Server;
use Zend\Stratigility\Middleware\NotFoundHandler;
use Zend\Stratigility\MiddlewarePipe;

class StratigilityServiceProvider implements ServiceProvider
{
    public function getServices()
    {
        return [
            Server::class => [self::class, 'createServer'],
            MiddlewarePipe::class => [self::class, 'createMiddlewarePipe'],
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
        $middlewaresQueue = $container->get(MiddlewareListServiceProvider::MIDDLEWARES_QUEUE);

        /* @var $middlewaresQueue \SplPriorityQueue */
        foreach ($middlewaresQueue as $middleware) {
            $app->pipe($middleware);
        }

        return $app;
    }

    public static function registerPageNotFoundMiddleware(ContainerInterface $container, callable $previous = null)
    {
        if ($previous) {
            $priorityQueue = $previous();
            $priorityQueue->insert(new NotFoundHandler(new Response()), MiddlewareOrder::PAGE_NOT_FOUND_LATE);
            return $priorityQueue;
        } else {
            throw new InvalidArgumentException("Could not find declaration for service '".MiddlewareListServiceProvider::MIDDLEWARES_QUEUE."'.");
        }

    }
}
