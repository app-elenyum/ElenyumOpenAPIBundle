<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elenyum\OpenAPI\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('elenyum_open_api');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('use_validation_groups')
                    ->info('If true, `groups` passed to @Model annotations will be used to limit validation constraints')
                    ->defaultFalse()
                ->end()
                ->arrayNode('cache')
                    ->validate()
                        ->ifTrue(function ($v) { return null !== $v['item_id'] && null === $v['pool']; })
                        ->thenInvalid('Can not set cache.item_id if cache.pool is null')
                    ->end()
                    ->children()
                        ->scalarNode('pool')
                            ->info('define cache pool to use')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('item_id')
                            ->info('define cache item id')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('documentation')
                    ->useAttributeAsKey('key')
                    ->info('The documentation used as base')
                    ->example(['info' => ['title' => 'My App']])
                    ->prototype('variable')->end()
                ->end()
                ->arrayNode('media_types')
                    ->info('List of enabled Media Types')
                    ->defaultValue(['json'])
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('areas')
                    ->info('Filter the routes that are documented')
                    ->defaultValue(
                        [
                            'default' => [
                                'path_patterns' => [],
                                'host_patterns' => [],
                                'with_annotation' => false,
                                'name_patterns' => [],
                                'disable_default_routes' => false,
                            ],
                        ]
                    )
                    ->beforeNormalization()
                        ->ifTrue(function ($v) {
                            return 0 === count($v) || isset($v['path_patterns']) || isset($v['host_patterns']);
                        })
                        ->then(function ($v) {
                            return ['default' => $v];
                        })
                    ->end()
                    ->validate()
                        ->ifTrue(function ($v) {
                            return !isset($v['default']);
                        })
                        ->thenInvalid('You must specify a `default` area under `nelmio_api_doc.areas`.')
                    ->end()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('path_patterns')
                                ->defaultValue([])
                                ->example(['^/api', '^/api(?!/admin)'])
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('host_patterns')
                                ->defaultValue([])
                                ->example(['^api\.'])
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('name_patterns')
                                ->defaultValue([])
                                ->example(['^api_v1'])
                                ->prototype('scalar')->end()
                            ->end()
                            ->booleanNode('with_annotation')
                                ->defaultFalse()
                                ->info('whether to filter by annotation')
                            ->end()
                            ->booleanNode('disable_default_routes')
                                ->defaultFalse()
                                ->info('if set disables default routes without annotations')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('models')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('use_jms')->defaultFalse()->end()
                    ->end()
                    ->children()
                        ->arrayNode('names')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('alias')->isRequired()->end()
                                    ->scalarNode('type')->isRequired()->end()
                                    ->variableNode('groups')
                                        ->defaultValue(null)
                                        ->validate()
                                            ->ifTrue(function ($v) { return null !== $v && !is_array($v); })
                                            ->thenInvalid('Model groups must be either `null` or an array.')
                                        ->end()
                                    ->end()
                                    ->arrayNode('areas')
                                        ->defaultValue([])
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}