<?php

namespace Elenyum\OpenAPI\Tests\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\PropertyDescriber\StringPropertyDescriber;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class StringPropertyDescriberTest extends TestCase
{
    private $stringPropertyDescriber;

    protected function setUp(): void
    {
        $this->stringPropertyDescriber = new StringPropertyDescriber();
    }

    public function testDescribe()
    {
        $type = new Type(Type::BUILTIN_TYPE_STRING);
        $property = new OA\Schema([]);

        $this->stringPropertyDescriber->describe([$type], $property);

        $this->assertEquals('string', $property->type);
    }

    public function testSupports()
    {
        $stringType = new Type(Type::BUILTIN_TYPE_STRING);
        $nonStringType = new Type(Type::BUILTIN_TYPE_INT); // Non-string type for negative test

        $this->assertTrue($this->stringPropertyDescriber->supports([$stringType]));
        $this->assertFalse($this->stringPropertyDescriber->supports([$nonStringType]));
    }
}