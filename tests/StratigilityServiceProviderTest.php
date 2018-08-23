<?php

namespace TheCodingMachine;

use PHPUnit\Framework\TestCase;
use Simplex\Container;
use Zend\HttpHandlerRunner\RequestHandlerRunner;

class StratigilityServiceProviderTest extends TestCase
{
    public function testServiceProvider(): void
    {
        $container = new Container([new StratigilityServiceProvider()]);
        $requestHandler = $container->get(RequestHandlerRunner::class);

        $this->assertInstanceOf(RequestHandlerRunner::class, $requestHandler);
    }
}
