<?php

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
                ->arrayNode('cache')
                    ->validate()
                        ->ifTrue(function ($v) { return null !== $v['item_id'] && null === $v['enable']; })
                        ->thenInvalid('Can not set cache.item_id if cache.enable is false')
                    ->end()
                    ->children()
                        ->booleanNode('enable')
                            ->info('define cache enable')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('item_id')
                            ->info('define cache item id')
                            ->defaultValue('elenyum_open_api')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('documentation')
                    ->useAttributeAsKey('key')
                    ->info('The documentation used as base')
                    ->example([
                        'info' => [
                            'title' => 'My App',
                            'description' => 'Description service',
                            'version' => '1.0.0'
                        ]
                    ])
                    ->prototype('variable')->end()
                ->end()
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
                    ->booleanNode('with_tag')
                    ->defaultTrue()
                    ->info('whether to filter by tag')
                ->end()
                    ->booleanNode('disable_default_routes')
                    ->defaultFalse()
                    ->info('if set disables default routes without annotations')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
