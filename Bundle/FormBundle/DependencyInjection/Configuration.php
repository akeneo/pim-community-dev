<?php

namespace Oro\Bundle\FormBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

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
                            if (isset($value['property']) && is_string($value['property'])) {
                                if (empty($value['properties'])) {
                                    $value['properties'] = array($value['property'] => array());
                                } else {
                                    throw new \Exception(
                                        'Option "property" cannot be set with option "properties".'
                                    );
                                }
                            }
                            unset($value['property']);
                            return $value;
                        })
                    ->end()
                    ->children()
                        ->arrayNode('form_options')
                            ->prototype('variable')->end()
                        ->end()
                        ->scalarNode('type')->isRequired()->end()
                        ->arrayNode('options')
                            ->useAttributeAsKey('options')->prototype('variable')->end()
                        ->end()
                        ->scalarNode('property')->end()
                        ->arrayNode('properties')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->beforeNormalization()
                                ->always(function($value) {
                                    foreach ($value as $k => $v) {
                                        if (!isset($v['name']) && !is_numeric($k)) {
                                            $v['name'] = $k;
                                            $value[] = $v;
                                            unset($value[$k]);
                                        }
                                    }
                                    return $value;
                                })
                            ->end()
                            ->prototype('variable')
                                ->beforeNormalization()
                                ->ifString()
                                    ->then(function($value) { return array('name' => $value); })
                                ->end()
                                ->validate()
                                    ->always(function($value) {
                                        if (!isset($value['name'])) {
                                            throw new \Exception('name is required option.');
                                        }
                                        return $value;
                                    })
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('entity_class')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('acl_resource')->end()
                        ->scalarNode('route')->defaultValue('oro_form_autocomplete_search')->end()
                        ->scalarNode('view')
                            ->cannotBeEmpty()
                            ->defaultValue('OroFormBundle:EntityAutocomplete:search.json.twig')
                        ->end()
                        ->scalarNode('url')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
