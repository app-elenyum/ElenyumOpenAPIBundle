<?php

namespace Elenyum\OpenAPI\Attribute;

use Attribute;
use OpenApi\Annotations\Parameter;
use OpenApi\Attributes\Attachable;
use OpenApi\Generator;
use ReflectionClass;
use Symfony\Component\Serializer\Attribute\Groups;

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
     * @param array $properties
     * @param string $type
     * @param array|null $groups
     * @param array|null $options
     * @param array $serializationContext
     * @throws \ReflectionException
     */
    public function __construct(
        array $properties = [],
        string $type = Generator::UNDEFINED,
        array $groups = null,
        array $options = null,
        array $serializationContext = []
    ) {
        if ($groups === null && isset($options['method']) && class_exists($type)) {
            $groups = array_merge($this->getEntityGroups($type, $options['method']), ['Default']);
        }

        parent::__construct($properties + [
                'type' => $type,
                'groups' => $groups,
                'options' => $options,
                'serializationContext' => $serializationContext,
            ]);
    }

    /**
     * @throws \ReflectionException
     */
    public function getEntityGroups(string $type, ?string $method = null): array
    {
        $reflectionClass = new ReflectionClass($type);

        $attributeGroups = $reflectionClass->getAttributes(Groups::class);
        if (isset($attributeGroups[0]) && isset($attributeGroups[0]?->getArguments()[0])) {
            $groups = $attributeGroups[0]->getArguments()[0];
            if (!empty($groups) && $method !== null) {
                $method = mb_strtoupper($method);
                $groups = preg_replace('/(\w+)/', $method.'_$1', $groups);
            }

            return $groups;
        }

        return [];
    }
}