<?php

namespace TheCodingMachine;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Zend\Diactoros\Server;
use Zend\Stratigility\MiddlewarePipe;

class StratigilityServiceProvider implements ServiceProvider
{
    public function getServices()
    {
        return [
            Server::class => [self::class, 'createServer'],
            MiddlewarePipe::class => [self::class, 'createMiddlewarePipe'],
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
}
