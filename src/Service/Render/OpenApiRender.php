<?php

namespace Elenyum\OpenAPI\Service\Render;

use OpenApi\Annotations\OpenApi;

interface OpenApiRender
{
    public function render(OpenApi $spec, array $options = []): string;
}
