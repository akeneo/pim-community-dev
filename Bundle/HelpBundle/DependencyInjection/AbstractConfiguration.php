<?php

namespace Oro\Bundle\HelpBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

abstract class AbstractConfiguration implements ConfigurationInterface
{
    /**
     * @param ArrayNodeDefinition $resourcesNode
     */
    protected function configureResourcesNodeDefinition(ArrayNodeDefinition $resourcesNode)
    {
        $self = $this;

        $resourcesNode
            ->useAttributeAsKey(true)
            ->beforeNormalization()
                ->always(
                    function (array $resources) use ($self) {
                        $self->assertKeysAreValidResourceNames($resources);
                        return $resources;
                    }
                )
            ->end()
            ->prototype('array')
                ->children()
                    ->scalarNode('server')
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
                    ->scalarNode('alias')->end()
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
    }

    /**
     * @param ArrayNodeDefinition $vendorsNode
     */
    protected function configureVendorsNodeDefinition(ArrayNodeDefinition $vendorsNode)
    {
        $self = $this;

        $vendorsNode
            ->useAttributeAsKey(true)
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
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $routesNode
     */
    protected function configureRoutesNodeDefinition(ArrayNodeDefinition $routesNode)
    {
        $self = $this;

        $routesNode
            ->useAttributeAsKey(true)
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
                    ->scalarNode('uri')->end()
                    ->scalarNode('link')->end()
                ->end()
            ->end();
    }

    /**
     * @param array $vendors
     * @throws InvalidConfigurationException
     */
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

    /**
     * @param array $resources
     * @throws InvalidConfigurationException
     */
    public function assertKeysAreValidResourceNames(array $resources)
    {
        foreach (array_keys($resources) as $resourceName) {
            if (!preg_match('/^[a-z_][a-z0-9_]*(:[a-z_][a-z0-9_]*){0,2}$/i', $resourceName)) {
                throw new InvalidConfigurationException(
                    sprintf('Node "resources" contains invalid resource name "%s".', $resourceName)
                );
            }
        }
    }
}
