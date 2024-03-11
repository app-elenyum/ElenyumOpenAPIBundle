<?php

namespace Elenyum\OpenAPI\Service\Render;

use OpenApi\Annotations\OpenApi;

/**
 * @internal
 */
class JsonOpenApiRenderer implements OpenApiRender
{
    public function render(OpenApi $spec, array $options = []): string
    {
        $options += [
            'no-pretty' => false,
        ];
        $flags = $options['no-pretty'] ? 0 : JSON_PRETTY_PRINT;

        return json_encode($spec, $flags | JSON_UNESCAPED_SLASHES);
    }
}
