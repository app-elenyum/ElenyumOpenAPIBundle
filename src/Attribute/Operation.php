<?php

namespace Elenyum\OpenAPI\Attribute;

use OpenApi\Annotations\Operation as BaseOperation;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Operation extends BaseOperation
{
}
