<?php

namespace Elenyum\OpenAPI\Tests;

use Elenyum\OpenAPI\ElenyumOpenAPIBundle;
use Elenyum\OpenAPI\DependencyInjection\ElenyumOpenAPIExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ElenyumOpenAPIBundleTest extends TestCase
{
    public function testPath()
    {
        $bundle = new ElenyumOpenAPIBundle();
        $expectedPath = dirname(dirname((new \ReflectionClass(ElenyumOpenAPIBundle::class))->getFileName()));

        $this->assertEquals($expectedPath, $bundle->getPath());
    }

    public function testBuild()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects($this->never())
            ->method('addCompilerPass'); // Добавьте специфический тип компилятора, который вы ожидаете здесь.

        $bundle = new ElenyumOpenAPIBundle();
        $bundle->build($container);
    }

    public function testGetContainerExtension()
    {
        $bundle = new ElenyumOpenAPIBundle();
        $extension = $bundle->getContainerExtension();

        $this->assertInstanceOf(ElenyumOpenAPIExtension::class, $extension);
    }
}