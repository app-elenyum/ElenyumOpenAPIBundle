<?php

namespace Elenyum\OpenAPI\Tests\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\PropertyDescriber\ObjectPropertyDescriber;
use Elenyum\OpenAPI\Service\Model\Model;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class ObjectPropertyDescriberTest extends TestCase
{
    private $objectPropertyDescriber;
    private $modelRegistry;

    protected function setUp(): void
    {
        $this->modelRegistry = $this->createMock(ModelRegistry::class);
        $this->objectPropertyDescriber = new ObjectPropertyDescriber();
        $this->objectPropertyDescriber->setModelRegistry($this->modelRegistry);
    }

    public function testDescribeWithNonNullableType()
    {
        $className = 'App\\Entity\\User';
        $type = new Type(Type::BUILTIN_TYPE_OBJECT, false, $className);
        $property = new OA\Schema([]);
        $groups = null;
        $context = [];
        $expectedRef = '#/components/schemas/User';

        $this->modelRegistry->expects($this->once())
            ->method('register')
            ->with($this->equalTo(new Model($type, $groups, null, $context)))
            ->willReturn($expectedRef);

        $this->objectPropertyDescriber->describe([$type], $property, $groups, null, $context);

        $this->assertEquals($expectedRef, $property->ref);
    }

    public function testDescribeWithNullableType()
    {
        $className = 'App\\Entity\\User';
        $type = new Type(Type::BUILTIN_TYPE_OBJECT, true, $className);
        $property = new OA\Schema([]);
        $groups = null;
        $context = [];
        $expectedRef = '#/components/schemas/User';

        // Simulating nullable object logic
        $this->modelRegistry->expects($this->any())
            ->method('register');

        $this->objectPropertyDescriber->describe([$type], $property, $groups, null, $context);

        $this->assertCount(1, $property->oneOf);
    }

    public function testSupports()
    {
        $objectType = new Type(Type::BUILTIN_TYPE_OBJECT, false, 'App\\Entity\\User');
        $stringType = new Type(Type::BUILTIN_TYPE_STRING); // Non-object type for negative test

        $this->assertTrue($this->objectPropertyDescriber->supports([$objectType]));
        $this->assertFalse($this->objectPropertyDescriber->supports([$stringType]));
    }
}