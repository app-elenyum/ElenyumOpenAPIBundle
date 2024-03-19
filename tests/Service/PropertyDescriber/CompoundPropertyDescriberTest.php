<?php

namespace Elenyum\OpenAPI\Tests\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\PropertyDescriber\CompoundPropertyDescriber;
use Elenyum\OpenAPI\Service\PropertyDescriber\PropertyDescriberInterface;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class CompoundPropertyDescriberTest extends TestCase
{
    private $compoundPropertyDescriber;
    private $modelRegistry;
    private $childPropertyDescriber;

    protected function setUp(): void
    {
        $this->modelRegistry = $this->createMock(ModelRegistry::class);
        $this->childPropertyDescriber = $this->createMock(PropertyDescriberInterface::class);

        $this->compoundPropertyDescriber = new CompoundPropertyDescriber();
        $this->compoundPropertyDescriber->setModelRegistry($this->modelRegistry);
        $this->compoundPropertyDescriber->setPropertyDescriber($this->childPropertyDescriber);
    }

    public function testDescribe()
    {
        $types = [new Type(Type::BUILTIN_TYPE_INT), new Type(Type::BUILTIN_TYPE_STRING)];
        $property = new OA\Schema([]);
        $groups = null;
        $context = [];

        // Create a schema mock to be returned by the Util::createChild method
        $schemaMock = $this->createMock(OA\Schema::class);
        // The Util class is not shown in the provided snippets but you would mock it here if necessary

        $this->childPropertyDescriber->expects($this->exactly(2))
            ->method('describe')
            ->willReturnCallback(
                function (array $types, OA\Schema $property) use ($schemaMock) {
                    $property->oneOf[] = $schemaMock;
                }
            );

        $this->compoundPropertyDescriber->describe($types, $property, $groups, $schemaMock, $context);

        $this->assertNotEmpty($property->oneOf);
        $this->assertCount(4, $property->oneOf);
        $this->assertContainsOnlyInstancesOf(OA\Schema::class, $property->oneOf);
    }

    public function testSupports()
    {
        $supportedTypes = [new Type(Type::BUILTIN_TYPE_INT), new Type(Type::BUILTIN_TYPE_STRING)];
        $unsupportedTypes = [new Type(Type::BUILTIN_TYPE_INT)]; // Only one type, so should be unsupported

        $this->assertTrue($this->compoundPropertyDescriber->supports($supportedTypes));
        $this->assertFalse($this->compoundPropertyDescriber->supports($unsupportedTypes));
    }
}