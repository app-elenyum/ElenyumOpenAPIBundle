<?php

namespace Elenyum\OpenAPI\Tests\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\PropertyDescriber\IntegerPropertyDescriber;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class IntegerPropertyDescriberTest extends TestCase
{
    private $integerPropertyDescriber;

    protected function setUp(): void
    {
        $this->integerPropertyDescriber = new IntegerPropertyDescriber();
    }

    public function testDescribe()
    {
        $type = new Type(Type::BUILTIN_TYPE_INT);
        $property = new OA\Schema([]);

        $this->integerPropertyDescriber->describe([$type], $property);

        $this->assertEquals('integer', $property->type);
    }

    public function testSupports()
    {
        $intType = new Type(Type::BUILTIN_TYPE_INT);
        $nonIntType = new Type(Type::BUILTIN_TYPE_FLOAT); // Non-integer type for negative test

        $this->assertTrue($this->integerPropertyDescriber->supports([$intType]));
        $this->assertFalse($this->integerPropertyDescriber->supports([$nonIntType]));
    }
}