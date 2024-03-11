<?php

namespace Elenyum\OpenAPI\Service\ModelDescriber;

use Elenyum\OpenAPI\Service\Model\Model;
use OpenApi\Annotations\Schema;

interface ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema);

    public function supports(Model $model): bool;
}
