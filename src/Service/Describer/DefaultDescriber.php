<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elenyum\OpenAPI\Service\Describer;

use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;

/**
 * Makes the swagger documentation valid even if there are missing fields.
 *
 * @author web-men <ibootta@gmail.com>
 */
final class DefaultDescriber implements DescriberInterface
{
    /**
     * All http method verbs as known by swagger.
     *
     * @var array
     */
    public const OPERATIONS = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head', 'trace'];

    public function __construct(
        private array $options = []
    ) {
    }

    public function describe(OA\OpenApi $api)
    {
        // Info
        /** @var OA\Info $info */
        $info = self::getChild($api, OA\Info::class);
        if (!empty($this->options['documentation']) && !empty($this->options['documentation']['info'])) {
            if (Generator::UNDEFINED === $info->title) {
                $info->title = $this->options['documentation']['info']['title'];
            }
            if (Generator::UNDEFINED === $info->version) {
                $info->version = $this->options['documentation']['info']['version'];
            }
            if (Generator::UNDEFINED === $info->description) {
                $info->description = $this->options['documentation']['info']['description'] ?? '';
            }
        }

        // Paths
        if (Generator::UNDEFINED === $api->paths) {
            $api->paths = [];
        }
        foreach ($api->paths as $path) {
            foreach (self::OPERATIONS as $method) {
                /** @var OA\Operation $operation */
                $operation = $path->{$method};
                if (Generator::UNDEFINED !== $operation && null !== $operation && (Generator::UNDEFINED === $operation->responses || empty($operation->responses))) {
                    /** @var OA\Response $response */
                    $response = self::getIndexedCollectionItem($operation, OA\Response::class, 'default');
                    $response->description = '';
                }
            }
        }
    }

    /**
     * Search for an Annotation within the $collection that has its member $index set to $value.
     *
     * @param string $member
     * @param mixed  $value
     *
     * @return false|int|string
     */
    public static function searchIndexedCollectionItem(array $collection, $member, $value)
    {
        return array_search($value, array_column($collection, $member), true);
    }

    /**
     * Create a new Object of $class with members $properties within $parent->{$collection}[]
     * and return the created index.
     *
     * @param string $collection
     * @param string $class
     */
    public static function createCollectionItem(OA\AbstractAnnotation $parent, $collection, $class, array $properties = []): int
    {
        if (Generator::UNDEFINED === $parent->{$collection}) {
            $parent->{$collection} = [];
        }

        $key = \count($parent->{$collection} ?: []);
        $parent->{$collection}[$key] = self::createChild($parent, $class, $properties);

        return $key;
    }

    /**
     * Return an existing nested Annotation from $parent->{$collection}[]
     * having its mapped $property set to $value.
     *
     * Create, add to $parent->{$collection}[] and set its member $property to $value otherwise.
     *
     * $collection is determined from $parent::$_nested[$class]
     * it is expected to be a double value array nested Annotation
     * with the second value being the mapping index $property.
     *
     * @see OA\AbstractAnnotation::$_nested
     *
     * @param string $class
     * @param mixed  $value
     */
    private static function getIndexedCollectionItem(OA\AbstractAnnotation $parent, $class, $value): OA\AbstractAnnotation
    {
        $nested = $parent::$_nested;
        [$collection, $property] = $nested[$class];

        $key = self::searchIndexedCollectionItem(
            $parent->{$collection} && Generator::UNDEFINED !== $parent->{$collection} ? $parent->{$collection} : [],
            $property,
            $value
        );

        if (false === $key) {
            $key = self::createCollectionItem($parent, $collection, $class, [$property => $value]);
        }

        return $parent->{$collection}[$key];
    }

    public static function getChild(OA\AbstractAnnotation $parent, $class, array $properties = []): OA\AbstractAnnotation
    {
        $nested = $parent::$_nested;
        $property = $nested[$class];

        if (null === $parent->{$property} || Generator::UNDEFINED === $parent->{$property}) {
            $parent->{$property} = self::createChild($parent, $class, $properties);
        }

        return $parent->{$property};
    }

    public static function createChild(OA\AbstractAnnotation $parent, $class, array $properties = []): OA\AbstractAnnotation
    {
        $nesting = self::getNestingIndexes($class);

        if (!empty(array_intersect(array_keys($properties), $nesting))) {
            throw new \InvalidArgumentException('Nesting Annotations is not supported.');
        }

        return new $class(
            array_merge($properties, ['_context' => self::createContext(['nested' => $parent], $parent->_context)])
        );
    }

    private static function getNestingIndexes($class): array
    {
        return array_values(array_map(
            function ($value) {
                return \is_array($value) ? $value[0] : $value;
            },
            $class::$_nested
        ));
    }

    /**
     * Create a new Context with members $properties and parent context $parent.
     *
     * @see Context
     */
    public static function createContext(array $properties = [], Context $parent = null): Context
    {
        return new Context($properties, $parent);
    }
}
