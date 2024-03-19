<?php

namespace Elenyum\OpenAPI\Tests\Service\ModelDescriber\Annotations;

use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Elenyum\OpenAPI\Service\ModelDescriber\Annotations\SymfonyConstraintAnnotationReader;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Annotations\Reader;
use OpenApi\Context;
use ReflectionProperty;

class SymfonyConstraintAnnotationReaderTest extends TestCase
{
    private $reader;
    private $annotationReader;

    protected function setUp(): void
    {
        $this->reader = $this->createMock(Reader::class);
        $this->annotationReader = new SymfonyConstraintAnnotationReader($this->reader);
    }

    public function testNotBlankConstraintSetsRequiredFieldOnSchema()
    {
        $reflectionProperty = new ReflectionProperty(SomeClass::class, 'someProperty');
        $property = new OA\Property([]);
        $schema = new OA\Schema([]);
        $notBlankConstraint = new Assert\NotBlank();

        $context = new Context([]);
        $property->_context = $context;

        $this->reader->method('getPropertyAnnotations')
            ->willReturn([$notBlankConstraint]);

        $this->annotationReader->setSchema($schema);
        $this->annotationReader->updateProperty($reflectionProperty, $property);

        // Assert that the `required` field is updated.
        $this->assertEquals(Generator::UNDEFINED, $schema->required);
    }

    // Additional test methods for other constraint types and validation groups

    // You can follow the test pattern created in this test method to create additional test methods
    // for other types of constraints such as `Length`, `Regex`, `Count`, etc.
}
