<?php

namespace Elenyum\OpenAPI\Tests\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\PropertyDescriber\ArrayPropertyDescriber;
use Elenyum\OpenAPI\Service\PropertyDescriber\PropertyDescriberInterface;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class ArrayPropertyDescriberTest extends TestCase
{
    private $modelRegistry;
    private $propertyDescriber;
    private $arrayPropertyDescriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelRegistry = $this->createMock(ModelRegistry::class);
        $this->propertyDescriber = $this->createMock(PropertyDescriberInterface::class);
        $this->arrayPropertyDescriber = new ArrayPropertyDescriber();
        $this->arrayPropertyDescriber->setModelRegistry($this->modelRegistry);
        $this->arrayPropertyDescriber->setPropertyDescriber($this->propertyDescriber);
    }

    public function testDescribe()
    {
        $type = new Type(Type::BUILTIN_TYPE_ARRAY, true, null, true);
        $property = new OA\Schema([]);
        $groups = null;
        $schema = new OA\Schema([]);
        $context = [];

        $this->propertyDescriber->expects($this->never())
            ->method('describe')
            ->with([$type], $this->isInstanceOf(OA\Schema::class), $groups, $schema, $context);

        $this->arrayPropertyDescriber->describe([$type], $property, $groups, $schema, $context);

        $this->assertSame('array', $property->type);
        $this->assertInstanceOf(OA\Items::class, $property->items);
    }

    public function testSupports()
    {
        $supportedType = new Type(Type::BUILTIN_TYPE_ARRAY, true, null, true, new Type(Type::BUILTIN_TYPE_INT));
        $unsupportedType = new Type(Type::BUILTIN_TYPE_OBJECT);

        $this->assertTrue($this->arrayPropertyDescriber->supports([$supportedType]));
        $this->assertFalse($this->arrayPropertyDescriber->supports([$unsupportedType]));
    }
}