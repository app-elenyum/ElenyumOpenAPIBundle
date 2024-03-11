<?php

namespace Elenyum\OpenAPI\Attribute;

use OpenApi\Annotations\AbstractAnnotation;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Security extends AbstractAnnotation
{
    /** {@inheritdoc} */
    public static $_types = [
        'name' => 'string',
        'scopes' => '[string]',
    ];

    public static $_required = ['name'];

    /**
     * @var string
     */
    public $name;

    /**
     * @var string[]
     */
    public $scopes = [];

    public function __construct(
        array $properties = [],
        string $name = null,
        array $scopes = []
    ) {
        parent::__construct($properties + [
            'name' => $name,
            'scopes' => $scopes,
        ]);
    }
}
