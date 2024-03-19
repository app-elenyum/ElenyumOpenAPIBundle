<?php

namespace Elenyum\OpenAPI\Tests\Service\Util;

use Elenyum\OpenAPI\Service\Util\ControllerReflector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ControllerReflectorTest extends TestCase
{
    private $container;
    private $controllerReflector;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->controllerReflector = new ControllerReflector($this->container);
    }

    public function testGetReflectionMethodWithClassMethodString()
    {
        $this->container
            ->expects($this->never())
            ->method('has');

        $controllerString = TestController::class . '::myAction';
        $method = $this->controllerReflector->getReflectionMethod($controllerString);

        $this->assertInstanceOf(\ReflectionMethod::class, $method);
        $this->assertSame('myAction', $method->getName());
    }

    public function testGetReflectionMethodWithServiceIdMethodString()
    {
        $fakeService = new class {
            public function myMethod() {}
        };

        $this->container
            ->expects($this->once())
            ->method('has')
            ->with('my_service')
            ->willReturn(true);

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with('my_service')
            ->willReturn($fakeService);

        $controllerString = 'my_service::myMethod';
        $method = $this->controllerReflector->getReflectionMethod($controllerString);

        $this->assertInstanceOf(\ReflectionMethod::class, $method);
        $this->assertSame('myMethod', $method->getName());
        $this->assertSame(get_class($fakeService), $method->getDeclaringClass()->getName());
    }
}