<?php

namespace Oro\Bundle\DataGridBundle\Extension\Pager;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('pager')
            ->prototype('array')
                ->ignoreExtraKeys()
                ->children()
                    ->scalarNode('maxPerPage')->end()
                ->end()
            ->end();

        return $builder;
    }
}
