<?php

namespace Elenyum\OpenAPI\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\ModelDescriber\ModelRegistryAwareInterface;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;

class CompoundPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface, PropertyDescriberAwareInterface
{
    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null, array $context = [])
    {
        $property->oneOf = Generator::UNDEFINED !== $property->oneOf ? $property->oneOf : [];

        foreach ($types as $type) {
            $property->oneOf[] = $schema = Util::createChild($property, OA\Schema::class, []);

            $this->propertyDescriber->describe([$type], $property, $groups, $schema, $context);
        }
    }

    public function supports(array $types): bool
    {
        return count($types) >= 2;
    }

    /**
     * @var ModelRegistry
     */
    private ModelRegistry $modelRegistry;

    public function setModelRegistry(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
    }

    /**
     * @var PropertyDescriberInterface
     */
    protected PropertyDescriberInterface $propertyDescriber;

    public function setPropertyDescriber(PropertyDescriberInterface $propertyDescriber): void
    {
        $this->propertyDescriber = $propertyDescriber;
    }
}
