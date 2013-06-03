<?php

namespace Oro\Bundle\FormBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oro_form');
        $rootNode
            ->children()
                ->arrayNode('autocomplete_entities')
                ->useAttributeAsKey('autocomplete_entities')
                ->prototype('array')
                    ->fixXmlConfig('property', 'properties')
                    ->beforeNormalization()
                        ->always(function($value) {
                            if (array_key_exists('property', $value)) {
                                if (!isset($value['properties'])) {
                                    if (!$value['property']) {
                                        throw new \Exception('Option "property" cannot be not empty.');
                                    } elseif (is_string($value['property'])) {
                                        $value['properties'] = array($value['property'] => array());
                                        unset($value['property']);
                                    } else {
                                        throw new \Exception('Option "property" must be a string.');
                                    }
                                } else {
                                    throw new \Exception('Option "property" cannot be used with option "properties".');
                                }
                            }
                            return $value;
                        })
                    ->end()
                    ->children()
                        ->scalarNode('type')->end()
                        ->arrayNode('options')
                            ->useAttributeAsKey('options')->prototype('variable')->end()
                        ->end()
                        ->arrayNode('properties')
                            ->beforeNormalization()
                                ->always(function($value) {
                                    foreach ($value as $k => $v) {
                                        if (!isset($v['property']) && !is_numeric($k)) {
                                            $value[$k]['property'] = $k;
                                        }
                                    }
                                    return $value;
                                })
                            ->end()
                            ->prototype('variable')
                                ->beforeNormalization()
                                ->ifString()
                                    ->then(function($value) { return array('property' => $value); })
                                ->end()
                                ->validate()
                                    ->always(function($value) {
                                        if (!isset($value['property'])) {
                                            throw new \Exception('property is required option.');
                                        }
                                        return $value;
                                    })
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('acl_resource')->end()
                        ->scalarNode('route')->end()
                        ->scalarNode('url')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
