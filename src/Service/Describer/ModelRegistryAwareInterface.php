<?php

namespace Elenyum\OpenAPI\Service\Describer;

use Elenyum\OpenAPI\Service\Model\ModelRegistry;

interface ModelRegistryAwareInterface
{
    public function setModelRegistry(ModelRegistry $modelRegistry);
}
