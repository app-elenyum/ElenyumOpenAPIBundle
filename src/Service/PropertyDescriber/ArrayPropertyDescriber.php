<?php

namespace Elenyum\OpenAPI\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\Describer\ModelRegistryAwareInterface;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

class ArrayPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface, PropertyDescriberAwareInterface
{

    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null, array $context = [])
    {
        $property->type = 'array';
        /** @var OA\Items $property */
        $property = Util::getChild($property, OA\Items::class);

        foreach ($types[0]->getCollectionValueTypes() as $type) {
            // Handle list pseudo type
            // https://symfony.com/doc/current/components/property_info.html#type-getcollectionkeytypes-type-getcollectionvaluetypes
            if ($this->supports([$type]) && empty($type->getCollectionValueTypes())) {
                continue;
            }

            $this->propertyDescriber->describe([$type], $property, $groups, $schema, $context);
        }
    }

    public function supports(array $types): bool
    {
        if (1 !== count($types) || !$types[0]->isCollection()) {
            return false;
        }

        if (empty($types[0]->getCollectionKeyTypes())) {
            return true;
        }

        return 1 === count($types[0]->getCollectionKeyTypes())
            && Type::BUILTIN_TYPE_INT === $types[0]->getCollectionKeyTypes()[0]->getBuiltinType();
    }

    /**
     * @var ModelRegistry
     */
    private $modelRegistry;

    public function setModelRegistry(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
    }

    /**
     * @var PropertyDescriberInterface
     */
    protected $propertyDescriber;

    public function setPropertyDescriber(PropertyDescriberInterface $propertyDescriber): void
    {
        $this->propertyDescriber = $propertyDescriber;
    }
}
