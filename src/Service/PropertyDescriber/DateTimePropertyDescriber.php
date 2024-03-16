<?php

namespace Elenyum\OpenAPI\Service\PropertyDescriber;

use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

class DateTimePropertyDescriber implements PropertyDescriberInterface
{
    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null, array $context = [])
    {
        $property->type = 'string';
        $property->format = 'date-time';
    }

    public function supports(array $types): bool
    {
        return 1 === count($types)
            && Type::BUILTIN_TYPE_OBJECT === $types[0]->getBuiltinType()
            && is_a($types[0]->getClassName(), \DateTimeInterface::class, true);
    }
}
