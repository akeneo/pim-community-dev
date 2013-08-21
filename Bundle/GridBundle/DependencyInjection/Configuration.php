<?php

namespace Oro\Bundle\GridBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const TRANSLATION_DOMAIN_NODE    = 'translation_domain';
    const DEFAULT_TRANSLATION_DOMAIN = 'messages';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oro_grid');

        $rootNode
            ->children()
                ->scalarNode(self::TRANSLATION_DOMAIN_NODE)
                ->cannotBeEmpty()
                ->defaultValue(self::DEFAULT_TRANSLATION_DOMAIN)
            ->end();

        return $treeBuilder;
    }
}
