<?php

namespace Elenyum\OpenAPI\Service\ModelDescriber;

use Elenyum\OpenAPI\Service\Model\ModelRegistry;

interface ModelRegistryAwareInterface
{
    public function setModelRegistry(ModelRegistry $modelRegistry);
}
