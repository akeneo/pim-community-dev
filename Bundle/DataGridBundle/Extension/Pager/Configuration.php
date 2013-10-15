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
            ->ignoreExtraKeys()
            ->children()
                ->booleanNode('enabled')->end()
                ->booleanNode('hide')->end()
                ->integerNode('default_per_page')->end()
                ->arrayNode('pageSize')
                    ->children()
                        ->booleanNode('hide')->end()
                        ->arrayNode('pageSize')
                            ->prototype('variable')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('pagination')
                    ->children()
                        ->booleanNode('hide')->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
