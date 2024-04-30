<?php

declare(strict_types=1);

namespace Elenyum\OpenAPI\Service\PropertyDescriber;

use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\ModelDescriber\ModelRegistryAwareInterface;
use OpenApi\Annotations as OA;

final class PropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface
{
    /** @var array<string, PropertyDescriberInterface[]> Recursion helper */
    private $called = [];

    /** @var PropertyDescriberInterface[] */
    private $propertyDescribers;

    public function __construct(
        iterable $propertyDescribers
    ) {
        $this->propertyDescribers = $propertyDescribers;
    }

    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null, array $context = []): void
    {
        if (!$propertyDescriber = $this->getPropertyDescriber($types)) {
            return;
        }

        $this->called[$this->getHash($types)][] = $propertyDescriber;
        $propertyDescriber->describe($types, $property, $groups, $schema, $context);
        $this->called = []; // Reset recursion helper
    }

    public function supports(array $types): bool
    {
        return null !== $this->getPropertyDescriber($types);
    }

    private function getHash(array $types): string
    {
        return md5(serialize($types));
    }

    private function getPropertyDescriber(array $types): ?PropertyDescriberInterface
    {
        foreach ($this->propertyDescribers as $propertyDescriber) {
            if ($propertyDescriber instanceof self) {
                continue;
            }

            // Prevent infinite recursion
            if (key_exists($this->getHash($types), $this->called)) {
                if (in_array($propertyDescriber, $this->called[$this->getHash($types)], true)) {
                    continue;
                }
            }

            if ($propertyDescriber instanceof ModelRegistryAwareInterface) {
                $propertyDescriber->setModelRegistry($this->modelRegistry);
            }

            if ($propertyDescriber instanceof PropertyDescriberAwareInterface) {
                $propertyDescriber->setPropertyDescriber($this);
            }

            if ($propertyDescriber->supports($types)) {
                return $propertyDescriber;
            }
        }

        return null;
    }

    /**
     * @var ModelRegistry
     */
    private $modelRegistry;

    public function setModelRegistry(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
    }
}
