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

        $self = $this;

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
                        ->arrayNode('vendors')
                            ->beforeNormalization()
                                ->always(
                                    function (array $vendors) use ($self) {
                                        $self->assertKeysAreValidVendorNames($vendors);
                                        return $vendors;
                                    }
                                )
                            ->end()
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('server')->end()
                                    ->scalarNode('prefix')->end()
                                    ->scalarNode('alias')->end()
                                    ->scalarNode('uri')->end()
                                    ->scalarNode('link')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();

        $this->configureResourcesNodeDefinition($nodeBuilder->arrayNode('resources'));

        return $treeBuilder;
    }

    public function assertKeysAreValidVendorNames(array $vendors)
    {
        foreach (array_keys($vendors) as $vendorName) {
            if (!preg_match('/^[a-z_][a-z0-9_]*$/i', $vendorName)) {
                throw new InvalidConfigurationException(
                    sprintf('Node "vendors" contains invalid vendor name "%s".', $vendorName)
                );
            }
        }
    }
}
