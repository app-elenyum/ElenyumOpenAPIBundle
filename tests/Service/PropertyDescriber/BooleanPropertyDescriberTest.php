<?php

namespace Elenyum\OpenAPI\Tests\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\PropertyDescriber\BooleanPropertyDescriber;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class BooleanPropertyDescriberTest extends TestCase
{
    private $booleanPropertyDescriber;

    protected function setUp(): void
    {
        $this->booleanPropertyDescriber = new BooleanPropertyDescriber();
    }

    public function testDescribe()
    {
        $type = new Type(Type::BUILTIN_TYPE_BOOL);
        $property = new OA\Schema([]);

        $this->booleanPropertyDescriber->describe([$type], $property);

        $this->assertEquals('boolean', $property->type);
    }

    public function testSupports()
    {
        $booleanType = new Type(Type::BUILTIN_TYPE_BOOL);
        $stringType = new Type(Type::BUILTIN_TYPE_STRING);

        $this->assertTrue($this->booleanPropertyDescriber->supports([$booleanType]));
        $this->assertFalse($this->booleanPropertyDescriber->supports([$stringType]));
    }
}