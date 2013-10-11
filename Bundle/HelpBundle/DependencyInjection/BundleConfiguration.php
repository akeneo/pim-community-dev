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

        $this->configureResourcesNodeDefinition($rootNode->children()->arrayNode('resources'));

        return $treeBuilder;
    }
}
