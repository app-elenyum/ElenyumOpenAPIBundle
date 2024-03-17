<?php

namespace Elenyum\OpenAPI\Service\Describer;

use OpenApi\Annotations\OpenApi;

interface DescriberInterface
{
    public function describe(OpenApi $api);
}
