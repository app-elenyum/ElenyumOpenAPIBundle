<?php

namespace Elenyum\OpenAPI\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\Model\Model;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\ModelDescriber\ModelRegistryAwareInterface;
use Elenyum\OpenAPI\Service\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

class ObjectPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface
{
    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null, array $context = [])
    {
        $type = new Type(
            $types[0]->getBuiltinType(),
            false,
            $types[0]->getClassName(),
            $types[0]->isCollection(),
            // BC layer for symfony < 5.3
            method_exists($types[0], 'getCollectionKeyTypes') ? $types[0]->getCollectionKeyTypes() : $types[0]->getCollectionKeyType(),
            method_exists($types[0], 'getCollectionValueTypes') ?
                ($types[0]->getCollectionValueTypes()[0] ?? null) :
                $types[0]->getCollectionValueType()
        ); // ignore nullable field

        if ($types[0]->isNullable()) {
            $weakContext = Util::createWeakContext($property->_context);
            $schemas = [new OA\Schema(['ref' => $this->modelRegistry->register(new Model($type, $groups, null, $context)), '_context' => $weakContext])];
            $property->oneOf = $schemas;

            return;
        }

        $property->ref = $this->modelRegistry->register(new Model($type, $groups, null, $context));
    }

    public function supports(array $types): bool
    {
        return 1 === count($types)
            && Type::BUILTIN_TYPE_OBJECT === $types[0]->getBuiltinType();
    }

    /**
     * @var ModelRegistry
     */
    private ModelRegistry $modelRegistry;

    public function setModelRegistry(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
    }
}
