<?php

namespace TheCodingMachine;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Zend\Diactoros\Server;
use Zend\Stratigility\MiddlewarePipe;

class StratigilityServiceProvider implements ServiceProvider
{
    const MIDDLEWARES_QUEUE = 'middlewaresQueue';
    const ORDER_UTILITY_EARLY = -50;
    const ORDER_UTILITY = 0;
    const ORDER_UTILITY_LATE = 50;
    const ORDER_ROUTER_EARLY = 950;
    const ORDER_ROUTER = 1000;
    const ORDER_ROUTER_LATE = 1050;
    const ORDER_PAGE_NOT_FOUND_EARLY = 1950;
    const ORDER_PAGE_NOT_FOUND = 2000;
    const ORDER_PAGE_NOT_FOUND_LATE = 2050;
    const ORDER_EXCEPTION_EARLY = 2950;
    const ORDER_EXCEPTION = 3000;
    const ORDER_EXCEPTION_LATE = 3050;


    public static function getServices()
    {
        return [
            Server::class => 'createServer',
            MiddlewarePipe::class => 'createMiddleware',
            'middlewaresQueue' => 'createPriorityQueue'
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
    public static function createMiddleware(ContainerInterface $container) : MiddlewarePipe
    {
        $app = new MiddlewarePipe();
        $middlewaresQueue = $container->get(self::MIDDLEWARES_QUEUE);
        /* @var $middlewaresQueue \SplPriorityQueue */
        foreach ($middlewaresQueue as $middleware) {
            $app->pipe($middleware);
        }
        return $app;
    }

    public static function createPriorityQueue() : \SplPriorityQueue
    {
        return new \SplPriorityQueue();
    }
}
