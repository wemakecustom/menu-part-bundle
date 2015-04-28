<?php

namespace WMC\MenuPartBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use WMC\MenuPartBundle\Menu\MenuProvider;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wmc_menu_part');

        // If array of strings, use values as keys with empty arrays as values.
        $valuesAsKeys = function(array $array) {
            if (isset($array[0]) && is_string($array[0])) {
                return array_combine(
                    array_values($array),
                    array_fill(0, count($array), array())
                );
            } else {
                return $array;
            }
        };

        $valueAsKey = function($value) {
            return array("${value}" => array());
        };

        $mergeAll = function(array $menus) {
            foreach ($menus as $name => $menu) {
                if ($name === '_all') {
                    continue;
                }

                $menus[$name] = MenuProvider::mergeOptions($menus['_all'], $menus[$name]);
            }

            return $menus;
        };

        $rootNode
            ->children()
                ->arrayNode('menus')
                    ->defaultValue(['_all' => []])
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->beforeNormalization()
                        ->ifArray()
                        ->then(function($v) {
                            // default global menus
                            if (!isset($v['_all'])) {
                                $v['_all'] = [];
                            }

                            return $v;
                        })
                    ->end()
                    ->validate()
                        ->always()
                        ->then($mergeAll)
                    ->end()
                    ->prototype('array')
                        ->children()
                            ->arrayNode('visitors')
                                ->requiresAtLeastOneElement()
                                ->useAttributeAsKey('name')
                                ->beforeNormalization()
                                    ->ifArray()
                                    ->then($valuesAsKeys)
                                ->end()
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then($valueAsKey)
                                ->end()
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('priority')
                                            ->defaultValue(10)
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('class')
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(function($v) { return explode(' ', $v); })
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('attributes')
                                ->requiresAtLeastOneElement()
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
