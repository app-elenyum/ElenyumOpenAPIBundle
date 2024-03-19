<?php

namespace Elenyum\OpenAPI\Tests\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\PropertyDescriber\FloatPropertyDescriber;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class FloatPropertyDescriberTest extends TestCase
{
    private $floatPropertyDescriber;

    protected function setUp(): void
    {
        $this->floatPropertyDescriber = new FloatPropertyDescriber();
    }

    public function testDescribe()
    {
        $type = new Type(Type::BUILTIN_TYPE_FLOAT);
        $property = new OA\Schema([]);

        $this->floatPropertyDescriber->describe([$type], $property);

        $this->assertEquals('number', $property->type);
        $this->assertEquals('float', $property->format);
    }

    public function testSupports()
    {
        $floatType = new Type(Type::BUILTIN_TYPE_FLOAT);
        $nonFloatType = new Type(Type::BUILTIN_TYPE_STRING); // Non-float type for negative test

        $this->assertTrue($this->floatPropertyDescriber->supports([$floatType]));
        $this->assertFalse($this->floatPropertyDescriber->supports([$nonFloatType]));
    }
}