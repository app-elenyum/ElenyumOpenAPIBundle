<?php

namespace Elenyum\OpenAPI\Tests\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\PropertyDescriber\NullablePropertyDescriber;
use Elenyum\OpenAPI\Service\PropertyDescriber\ObjectPropertyDescriber;
use Elenyum\OpenAPI\Service\PropertyDescriber\PropertyDescriber;
use Elenyum\OpenAPI\Service\PropertyDescriber\PropertyDescriberInterface;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class PropertyDescriberTest extends TestCase
{
    private $modelRegistry;

    protected function setUp(): void
    {
        $this->modelRegistry = $this->createMock(ModelRegistry::class);
    }

    public function testDescribeDelegatesProperly()
    {
        $intDescriber = $this->createMock(PropertyDescriberInterface::class);
        $floatDescriber = $this->createMock(PropertyDescriberInterface::class);

        $intDescriber->expects($this->any())
            ->method('supports')
            ->willReturnCallback(fn($types) => $types[0]->getBuiltinType() === 'int');

        $floatDescriber->expects($this->any())
            ->method('supports')
            ->willReturnCallback(fn($types) => $types[0]->getBuiltinType() === 'float');

        $intDescriber->expects($this->once())
            ->method('describe')
            ->with($this->callback(fn($types) => $types[0]->getBuiltinType() === 'int'));

        $floatDescriber->expects($this->never())
            ->method('describe');

        $propertyDescriber = new PropertyDescriber(new \ArrayIterator([$intDescriber, $floatDescriber]));
        $propertyDescriber->setModelRegistry($this->modelRegistry);

        $typeInt = new Type(Type::BUILTIN_TYPE_INT);
        $propertyInt = new OA\Schema([]);

        $propertyDescriber->describe([$typeInt], $propertyInt);
    }

    public function testSupports()
    {
        $intDescriber = $this->createMock(PropertyDescriberInterface::class);

        $intDescriber->method('supports')
            ->willReturnCallback(fn($types) => $types[0]->getBuiltinType() === 'int');

        $propertyDescriber = new PropertyDescriber(new \ArrayIterator([$intDescriber]));
        $propertyDescriber->setModelRegistry($this->modelRegistry);

        $typeInt = new Type(Type::BUILTIN_TYPE_INT);
        $typeFloat = new Type(Type::BUILTIN_TYPE_FLOAT);

        $this->assertTrue($propertyDescriber->supports([$typeInt]));
        $this->assertFalse($propertyDescriber->supports([$typeFloat]));
    }

    public function testWithoutProperty()
    {
        $propertyDescriber = new PropertyDescriber(new \ArrayIterator([]));
        $propertyDescriber->setModelRegistry($this->modelRegistry);

        $typeInt = new Type(Type::BUILTIN_TYPE_INT);
        $typeFloat = new Type(Type::BUILTIN_TYPE_FLOAT);

        $propertyInt = new OA\Schema([]);

        $propertyDescriber->describe([$typeInt], $propertyInt);

        $this->assertFalse($propertyDescriber->supports([$typeInt]));
        $this->assertFalse($propertyDescriber->supports([$typeFloat]));
    }

    public function testWithProperty()
    {
        $objectModelDescriberDescriber = $this->createMock(ObjectPropertyDescriber::class);
        $nullablePropertyDescriber = $this->createMock(NullablePropertyDescriber::class);
        $objectModelDescriberDescriber->method('setModelRegistry')
            ->with($this->modelRegistry);

        $propertyDescriber = new PropertyDescriber(new \ArrayIterator([$objectModelDescriberDescriber, $nullablePropertyDescriber]));
        $propertyDescriber->setModelRegistry($this->modelRegistry);

        $typeInt = new Type(Type::BUILTIN_TYPE_INT);
        $typeFloat = new Type(Type::BUILTIN_TYPE_FLOAT);

        $propertyInt = new OA\Schema([]);

        $propertyDescriber->describe([$typeInt], $propertyInt);

        $this->assertFalse($propertyDescriber->supports([$typeInt]));
        $this->assertFalse($propertyDescriber->supports([$typeFloat]));
    }


    protected function tearDown(): void
    {
        // Clean-up logic if necessary
    }
}