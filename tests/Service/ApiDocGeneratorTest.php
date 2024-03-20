<?php

namespace Elenyum\OpenAPI\Tests\Service;

use Elenyum\OpenAPI\Service\ApiDocGenerator;
use Elenyum\OpenAPI\Service\Describer\DefaultDescriber;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\Request;

class ApiDocGeneratorTest extends TestCase
{
    public function testCache()
    {
        $adapter = new ArrayAdapter();
        $generator = new ApiDocGenerator([new DefaultDescriber()], [], $adapter, null, new Generator());
        $generator->setRequest(new Request());
        $this->assertJson(json_encode($generator->generate()));
    }

    public function testCacheWithCustomId()
    {
        $adapter = new ArrayAdapter();
        $generator = new ApiDocGenerator([new DefaultDescriber()], [], $adapter, 'custom_id', new Generator());
        $generator->setRequest(new Request());
        $this->assertJson(json_encode($generator->generate()));
    }
}
