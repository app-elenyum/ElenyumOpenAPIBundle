<?php

namespace Elenyum\OpenAPI\Service\ModelDescriber\Annotations;

use Doctrine\Common\Annotations\Reader;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use OpenApi\Annotations as OA;
use OpenApi\Generator;

/**
 * @internal
 */
class AnnotationsReader
{
    private $phpDocReader;
    private $openApiAnnotationsReader;
    private $symfonyConstraintAnnotationReader;

    public function __construct(
        ?Reader $annotationsReader,
        ModelRegistry $modelRegistry,
        bool $useValidationGroups = false
    ) {
        $this->phpDocReader = new PropertyPhpDocReader();
        $this->openApiAnnotationsReader = new OpenApiAnnotationsReader($annotationsReader, $modelRegistry);
        $this->symfonyConstraintAnnotationReader = new SymfonyConstraintAnnotationReader(
            $annotationsReader,
            $useValidationGroups
        );
    }

    public function updateDefinition(\ReflectionClass $reflectionClass, OA\Schema $schema): UpdateClassDefinitionResult
    {
        $this->openApiAnnotationsReader->updateSchema($reflectionClass, $schema);
        $this->symfonyConstraintAnnotationReader->setSchema($schema);

        return new UpdateClassDefinitionResult(
            $this->shouldDescribeModelProperties($schema)
        );
    }

    public function getPropertyName($reflection, string $default): string
    {
        return $this->openApiAnnotationsReader->getPropertyName($reflection, $default);
    }

    public function updateProperty($reflection, OA\Property $property, array $serializationGroups = null): void
    {
        $this->openApiAnnotationsReader->updateProperty($reflection, $property, $serializationGroups);
        $this->phpDocReader->updateProperty($reflection, $property);
        $this->symfonyConstraintAnnotationReader->updateProperty($reflection, $property, $serializationGroups);
    }

    /**
     * if an objects schema type and ref are undefined OR the object was manually
     * defined as an object, then we're good to do the normal describe flow of
     * class properties.
     */
    private function shouldDescribeModelProperties(OA\Schema $schema): bool
    {
        return (Generator::UNDEFINED === $schema->type || 'object' === $schema->type)
            && Generator::UNDEFINED === $schema->ref;
    }
}
