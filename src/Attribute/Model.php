<?php

namespace Elenyum\OpenAPI\Attribute;

use Attribute;
use OpenApi\Annotations\Parameter;
use OpenApi\Attributes\Attachable;
use OpenApi\Generator;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Model extends Attachable
{
    /** {@inheritdoc} */
    public static $_types = [
        'type' => 'string',
        'groups' => '[string]',
        'options' => '[mixed]',
    ];

    public static $_required = ['type'];

    public static $_parents = [
        Parameter::class,
    ];

    /**
     * @var string
     */
    public $type;

    /**
     * @var string[]
     */
    public $groups;

    /**
     * @var mixed[]
     */
    public $options;

    /**
     * @var array<string, mixed>
     */
    public $serializationContext;

    /**
     * @param mixed[]              $properties
     * @param string[]             $groups
     * @param mixed[]              $options
     * @param array<string, mixed> $serializationContext
     */
    public function __construct(
        array $properties = [],
        string $type = Generator::UNDEFINED,
        array $groups = null,
        array $options = null,
        array $serializationContext = []
    ) {
        parent::__construct($properties + [
                'type' => $type,
                'groups' => $groups,
                'options' => $options,
                'serializationContext' => $serializationContext,
            ]);
    }
}