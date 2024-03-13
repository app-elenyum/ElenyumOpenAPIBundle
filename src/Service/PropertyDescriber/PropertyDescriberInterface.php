<?php

namespace Elenyum\OpenAPI\Service\PropertyDescriber;

use OpenApi\Annotations\Schema;
use Symfony\Component\PropertyInfo\Type;

interface PropertyDescriberInterface
{
    /**
     * @param Type[]               $types
     * @param string[]|null        $groups  Deprecated use $context['groups'] instead
     * @param Schema               $schema  Allows to make changes inside of the schema (e.g. adding required fields)
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function describe(array $types, Schema $property, array $groups = null /* , ?Schema $schema = null */ /* , array $context = [] */);

    /**
     * @param Type[] $types
     */
    public function supports(array $types): bool;
}
