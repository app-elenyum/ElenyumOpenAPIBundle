<?php

namespace Elenyum\OpenAPI\Tests\Service;

use Elenyum\OpenAPI\Service\ApiDocGenerator;
use Elenyum\OpenAPI\Service\Describer\DefaultDescriber;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ApiDocGeneratorTest extends TestCase
{
    public function testCache()
    {
        $adapter = new ArrayAdapter();
        $generator = new ApiDocGenerator([new DefaultDescriber()], [], $adapter, null, new Generator());

        $this->assertEquals(json_encode($generator->generate()), json_encode($adapter->getItem('openapi_doc')->get()));
    }

    public function testCacheWithCustomId()
    {
        $adapter = new ArrayAdapter();
        $generator = new ApiDocGenerator([new DefaultDescriber()], [], $adapter, 'custom_id', new Generator());

        $this->assertEquals(json_encode($generator->generate()), json_encode($adapter->getItem('custom_id')->get()));
    }
}
