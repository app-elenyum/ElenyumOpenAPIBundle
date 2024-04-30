<?php

namespace Elenyum\OpenAPI\Attribute;

use OpenApi\Attributes\Tag as BaseTag;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Tag extends BaseTag
{

}