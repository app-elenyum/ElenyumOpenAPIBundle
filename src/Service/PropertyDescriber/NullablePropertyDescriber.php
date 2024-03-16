<?php

namespace Elenyum\OpenAPI\Service\PropertyDescriber;

use OpenApi\Annotations as OA;
use OpenApi\Generator;

final class NullablePropertyDescriber implements PropertyDescriberInterface, PropertyDescriberAwareInterface
{
    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null, array $context = [])
    {
        if (Generator::UNDEFINED === $property->nullable) {
            $property->nullable = true;
        }

        $this->propertyDescriber->describe($types, $property, $groups, $schema, $context);
    }

    public function supports(array $types): bool
    {
        foreach ($types as $type) {
            if ($type->isNullable()) {
                return true;
            }
        }

        return false;
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
