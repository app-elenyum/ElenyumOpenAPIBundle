<?php

namespace Elenyum\OpenAPI\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\Describer\ModelRegistryAwareInterface;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

final class DictionaryPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface, PropertyDescriberAwareInterface
{
    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null, array $context = [])
    {
        $property->type = 'object';
        /** @var OA\AdditionalProperties $additionalProperties */
        $additionalProperties = Util::getChild($property, OA\AdditionalProperties::class);

        $this->propertyDescriber->describe($types[0]->getCollectionValueTypes(), $additionalProperties, $groups, $schema, $context);
    }

    /** {@inheritDoc} */
    public function supports(array $types): bool
    {
        return 1 === count($types)
            && $types[0]->isCollection()
            && 1 === count($types[0]->getCollectionKeyTypes())
            && Type::BUILTIN_TYPE_STRING === $types[0]->getCollectionKeyTypes()[0]->getBuiltinType();
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
