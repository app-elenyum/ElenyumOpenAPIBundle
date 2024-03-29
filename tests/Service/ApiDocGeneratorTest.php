<?php

namespace Elenyum\OpenAPI\Tests\Service;

use ArrayIterator;
use Elenyum\OpenAPI\Service\ApiDocGenerator;
use Elenyum\OpenAPI\Service\Describer\DescriberInterface;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\OpenApiPhp\ModelRegister;
use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi;
use OpenApi\Context;
use OpenApi\Generator;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use PHPUnit\Framework\TestCase;

class ApiDocGeneratorTest extends TestCase
{
    protected function setUp(): void
    {
        $this->describer1 = $this->createMock(DescriberInterface::class);
        $this->modelRegistry = $this->createMock(ModelRegistry::class);
        $this->analysis = $this->createMock(Analysis::class);
        $this->modelRegister = $this->createMock(ModelRegister::class);
        $this->openApi = $this->createMock(OpenApi::class);
        $this->openApi->_context = new Context(['']);
        $this->cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $this->generator = $this->createMock(Generator::class);

    }

    public function testGenerate()
    {
        $openApi = new OpenApi([]);

        $generator = new ApiDocGenerator(
            new ArrayIterator($this->describer1),
            $this->modelRegistry,
            $this->analysis,
            $this->modelRegister,
            $this->openApi,
            $this->cacheItemPool,
            $this->generator,
            ['cache' => ['enable' => true]]
        );

        // Set expectations for cache
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $this->cacheItemPool->expects($this->once())
            ->method('getItem')
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('isHit')
            ->willReturn(false);

        $this->cacheItemPool->expects($this->once())
            ->method('save')
            ->with($cacheItem);

        // Fake the OpenApi object generation
        $this->generator
            ->method('generate')
            ->willReturn($openApi);

        // Call the generate method and assert the OpenApi object is returned
        $result = $generator->generate();

        $this->assertInstanceOf(OpenApi::class, $result);
    }

    // ...

    public function testGenerateWithGroupAndCacheHit()
    {
        $cacheItemMock = $this->createMock(CacheItemInterface::class);
        $cacheItemMock->expects($this->once())->method('isHit')->willReturn(true);
        $cacheItemMock->expects($this->once())->method('get')->willReturn(new OpenApi([]));

        $this->cacheItemPool->expects($this->once())->method('getItem')->willReturn($cacheItemMock);

        $generator = new ApiDocGenerator(
            new ArrayIterator($this->describer1),
            $this->modelRegistry,
            $this->analysis,
            $this->modelRegister,
            $this->openApi,
            $this->cacheItemPool,
            $this->generator,
            ['cache' => ['enable' => true]]
        );

        $generator->setGroup('someGroup');
        $result = $generator->generate();

        $this->assertInstanceOf(OpenApi::class, $result);
    }

    public function testGenerateWithoutCacheEnabled()
    {
        $generator = new ApiDocGenerator(
            new ArrayIterator($this->describer1),
            $this->modelRegistry,
            $this->analysis,
            $this->modelRegister,
            $this->openApi,
            $this->cacheItemPool,
            $this->generator,
            ['cache' => ['enable' => false]]
        );

        $this->cacheItemPool->expects($this->never())->method('getItem');
        $this->cacheItemPool->expects($this->never())->method('save');

        // mock the behavior of describers and model describers as needed

        $result = $generator->generate();

        $this->assertInstanceOf(OpenApi::class, $result);
    }

}