<?php

namespace Elenyum\OpenAPI\Tests\Service\OpenApiPhp;

use Elenyum\OpenAPI\Service\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    public function testGetPath()
    {
        $api = new OA\OpenApi([]);
        $path = '/test';

        $pathItem = Util::getPath($api, $path);

        $this->assertInstanceOf(OA\PathItem::class, $pathItem);
        $this->assertContains($pathItem, $api->paths);
    }

    public function testGetSchema()
    {
        $api = new OA\OpenApi([]);
        $schema = 'TestSchema';

        $schemaObject = Util::getSchema($api, $schema);

        $this->assertInstanceOf(OA\Schema::class, $schemaObject);
        $this->assertContains($schemaObject, $api->components->schemas);
    }

    // Additional tests to cover other static methods...

    // Depending on the internals of the methods you may need to use reflection to access private methods
    // or to set up the state necessary for each test,
    // since many of these methods query and manipulate the annotations of other objects.
}