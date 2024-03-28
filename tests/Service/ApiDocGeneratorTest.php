<?php

namespace Elenyum\OpenAPI\Tests\Service;

use Elenyum\OpenAPI\Service\ApiDocGenerator;
use Elenyum\OpenAPI\Service\Describer\DescriberInterface;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\ModelDescriber\ObjectModelDescriber;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

class ApiDocGeneratorTest extends TestCase
{
    private $describer1;
    private $modelDescriber1;
    private $modelDescriber2;
    private $cacheItemPool;
    private $logger;
    private $generator;

    protected function setUp(): void
    {
        $this->describer1 = $this->createMock(DescriberInterface::class);
        $this->modelDescriber1 = $this->createMock(ModelRegistry::class);
        $this->modelDescriber2 = $this->createMock(ObjectModelDescriber::class);
        $this->cacheItemPool = $this->createMock(CacheItemPoolInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->generator = $this->createMock(Generator::class);
    }

    public function testGenerate()
    {
        $openApi = new OpenApi([]);

        $generator = new ApiDocGenerator(
            [$this->describer1],
            [$this->modelDescriber1, $this->modelDescriber2],
            $this->cacheItemPool,
            'elenyum_open_api',
            $this->generator,
            ['cache' => ['enable' => true]]
        );

        $generator->setLogger($this->logger);

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
            [$this->describer1],
            [$this->modelDescriber1],
            $this->cacheItemPool,
            'elenyum_open_api',
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
            [$this->describer1],
            [$this->modelDescriber1],
            $this->cacheItemPool,
            null,
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