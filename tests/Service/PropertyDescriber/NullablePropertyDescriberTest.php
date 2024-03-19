<?php

namespace Elenyum\OpenAPI\Tests\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\PropertyDescriber\NullablePropertyDescriber;
use Elenyum\OpenAPI\Service\PropertyDescriber\PropertyDescriberInterface;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class NullablePropertyDescriberTest extends TestCase
{
    private $nullablePropertyDescriber;
    private $decoratedDescriber;

    protected function setUp(): void
    {
        $this->decoratedDescriber = $this->createMock(PropertyDescriberInterface::class);
        $this->nullablePropertyDescriber = new NullablePropertyDescriber();
        $this->nullablePropertyDescriber->setPropertyDescriber($this->decoratedDescriber);
    }

    public function testDescribe()
    {
        $type = new Type(Type::BUILTIN_TYPE_INT, true);
        $property = new OA\Schema([]);

        $this->decoratedDescriber->expects($this->once())
            ->method('describe')
            ->with([$type], $this->equalTo($property));

        $this->nullablePropertyDescriber->describe([$type], $property);

        $this->assertTrue($property->nullable);
    }

    public function testDescribeWithoutUndefinedProperty()
    {
        $type = new Type(Type::BUILTIN_TYPE_INT, true);
        $property = new OA\Schema([]);
        $property->nullable = false;

        $this->decoratedDescriber->expects($this->once())
            ->method('describe')
            ->with([$type], $this->equalTo($property));

        $this->nullablePropertyDescriber->describe([$type], $property);

        $this->assertFalse($property->nullable);
    }

    public function testSupports()
    {
        $nullableType = new Type(Type::BUILTIN_TYPE_INT, true);
        $nonNullableType = new Type(Type::BUILTIN_TYPE_INT);

        $this->assertTrue($this->nullablePropertyDescriber->supports([$nullableType]));
        $this->assertFalse($this->nullablePropertyDescriber->supports([$nonNullableType]));
    }
}