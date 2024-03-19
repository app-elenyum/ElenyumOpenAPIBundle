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
        $context = new Context([]);
        $property->_context = $context;
        $schema = new OA\Schema(['test' => $property]);
        $notBlankConstraint = new Assert\NotBlank();

        $this->reader->method('getPropertyAnnotations')
            ->willReturn([$notBlankConstraint]);

        $this->annotationReader->setSchema($schema);
        $this->annotationReader->updateProperty($reflectionProperty, $property);

        // Assert that the `required` field is updated.
        $this->assertEquals(Generator::UNDEFINED, $schema->required);
    }

}
