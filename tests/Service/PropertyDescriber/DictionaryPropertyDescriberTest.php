<?php

namespace Elenyum\OpenAPI\Tests\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\PropertyDescriber\DictionaryPropertyDescriber;
use Elenyum\OpenAPI\Service\PropertyDescriber\PropertyDescriberInterface;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class DictionaryPropertyDescriberTest extends TestCase
{
    private DictionaryPropertyDescriber $dictionaryPropertyDescriber;
    private ModelRegistry $modelRegistry;
    private PropertyDescriberInterface $childPropertyDescriber;

    protected function setUp(): void
    {
        $this->modelRegistry = $this->createMock(ModelRegistry::class);
        $this->childPropertyDescriber = $this->createMock(PropertyDescriberInterface::class);

        $this->dictionaryPropertyDescriber = new DictionaryPropertyDescriber();
        $this->dictionaryPropertyDescriber->setModelRegistry($this->modelRegistry);
        $this->dictionaryPropertyDescriber->setPropertyDescriber($this->childPropertyDescriber);
    }

    public function testDescribe()
    {
        $keyType = new Type(Type::BUILTIN_TYPE_STRING);
        $valueType = new Type(Type::BUILTIN_TYPE_INT);

        // Create a dictionary type
        $type = new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true, $keyType, $valueType);
        $types = [$type];
        $property = new OA\Schema([]);

        $additionalProperties = new OA\Schema([]);

        // Assume Util::getChild returns the $additionalProperties
        // Util cannot be tested here unless you mock static methods
        $this->childPropertyDescriber->expects($this->once())
            ->method('describe');

        $this->dictionaryPropertyDescriber->describe($types, $property);

        $this->assertSame('object', $property->type);
        $this->assertInstanceOf(OA\AdditionalProperties::class, $property->additionalProperties);
    }

    public function testSupports()
    {
        // This is a dictionary type (associative array)
        $dictionaryType = new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true,
            new Type(Type::BUILTIN_TYPE_STRING),
            new Type(Type::BUILTIN_TYPE_NULL)
        );

        // This is not a dictionary type
        $nonDictionaryType = new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true,
            new Type(Type::BUILTIN_TYPE_INT),
            new Type(Type::BUILTIN_TYPE_NULL)
        );

        $this->assertTrue($this->dictionaryPropertyDescriber->supports([$dictionaryType]));
        $this->assertFalse($this->dictionaryPropertyDescriber->supports([$nonDictionaryType]));
    }
}