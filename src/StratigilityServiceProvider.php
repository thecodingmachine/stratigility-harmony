<?php

namespace TheCodingMachine;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Zend\Diactoros\Server;
use Zend\Stratigility\MiddlewarePipe;

class StratigilityServiceProvider implements ServiceProvider
{
    public static function getServices()
    {
        return [
            Server::class => 'createServer',
            MiddlewarePipe::class => 'createMiddlewarePipe',
        ];
    }

    public static function createServer(ContainerInterface $container) : Server
    {
        // Decode json parameters for POST request
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'json')) {
            $postdata = file_get_contents('php://input');
            $postdata = json_decode($postdata, true);
        } else {
            $postdata = $_POST;
        }

        $server = Server::createServer($container->get(MiddlewarePipe::class), $_SERVER, $_GET, $postdata, $_COOKIE, $_FILES);

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

        /* @var $exceptionMiddlewaresQueue \SplPriorityQueue */
        $exceptionMiddlewaresQueue = $container->get(MiddlewareListServiceProvider::MIDDLEWARES_STRATIGILITY_EXCEPTION_QUEUE);

        foreach ($exceptionMiddlewaresQueue as $middleware) {
            $app->pipe($middleware);
        }

        return $app;
    }
}
