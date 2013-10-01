<?php

namespace Oro\Bundle\CronBundle\DependencyInjection;

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

        $builder
            ->root('oro_cron')
            ->children()
                ->scalarNode('max_concurrent_jobs')
                    ->defaultValue(5)
                ->end()
                ->booleanNode('jms_statistics')->defaultTrue()->end()
            ->end();

        return $builder;
    }
}
