<?php

namespace Elenyum\OpenAPI\Attribute;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class Module
{
    /**
     * @param string $name
     * @param Model $model
     * @param array|null $groups
     * @param array|null $options
     */
    public function __construct(
        public string $name,
        public Model $model,
        public ?array $groups = null,
        public ?array $options = null,
    ) {
    }
}