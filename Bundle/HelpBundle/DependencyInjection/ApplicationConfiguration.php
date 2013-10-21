<?php

namespace Oro\Bundle\HelpBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ApplicationConfiguration extends AbstractConfiguration
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oro_help');

        $nodeBuilder = $rootNode
            ->children()
                ->arrayNode('defaults')
                    ->isRequired()
                    ->children()
                        ->scalarNode('server')
                            ->cannotBeEmpty()
                            ->isRequired()
                            ->validate()
                                ->ifTrue(
                                    function ($value) {
                                        return !filter_var($value, FILTER_VALIDATE_URL);
                                    }
                                )
                                ->thenInvalid('Invalid URL %s.')
                            ->end()
                        ->end()
                        ->scalarNode('prefix')->end()
                        ->scalarNode('uri')->end()
                        ->scalarNode('link')
                            ->validate()
                                ->ifTrue(
                                    function ($value) {
                                        return !filter_var($value, FILTER_VALIDATE_URL);
                                    }
                                )
                                ->thenInvalid('Invalid URL %s.')
                            ->end()
                        ->end()
                    ->end()
                ->end();

        $this->configureResourcesNodeDefinition($nodeBuilder->arrayNode('resources'));
        $this->configureVendorsNodeDefinition($nodeBuilder->arrayNode('vendors'));
        $this->configureRoutesNodeDefinition($nodeBuilder->arrayNode('routes'));

        return $treeBuilder;
    }
}
