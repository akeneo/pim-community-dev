<?php

namespace Oro\Bundle\HelpBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class BundleConfiguration extends AbstractConfiguration
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('resources');

        $nodeBuilder = $rootNode->children();

        $this->configureResourcesNodeDefinition($nodeBuilder->arrayNode('resources'));
        $this->configureVendorsNodeDefinition($nodeBuilder->arrayNode('vendors'));
        $this->configureRoutesNodeDefinition($nodeBuilder->arrayNode('routes'));

        return $treeBuilder;
    }
}
