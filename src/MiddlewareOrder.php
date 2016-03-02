<?php

namespace TheCodingMachine;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Zend\Diactoros\Server;
use Zend\Stratigility\MiddlewarePipe;

class MiddlewareOrder
{
    const UTILITY_EARLY = -50;
    const UTILITY = 0;
    const UTILITY_LATE = 50;
    const ROUTER_EARLY = 950;
    const ROUTER = 1000;
    const ROUTER_LATE = 1050;
    const PAGE_NOT_FOUND_EARLY = 1950;
    const PAGE_NOT_FOUND = 2000;
    const PAGE_NOT_FOUND_LATE = 2050;
    const EXCEPTION_EARLY = 2950;
    const EXCEPTION = 3000;
    const EXCEPTION_LATE = 3050;
}
