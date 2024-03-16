<?php

namespace Elenyum\OpenAPI\Service\PropertyDescriber;

use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

class FloatPropertyDescriber implements PropertyDescriberInterface
{
    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null, array $context = [])
    {
        $property->type = 'number';
        $property->format = 'float';
    }

    public function supports(array $types): bool
    {
        return 1 === count($types) && Type::BUILTIN_TYPE_FLOAT === $types[0]->getBuiltinType();
    }
}
