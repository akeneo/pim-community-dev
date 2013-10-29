<?php

namespace Oro\Bundle\CalendarBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder
            ->root('oro_calendar')
            ->children()
                // A time before a calendar event occurs to get a reminder message. Defaults to 15 minutes.
                ->scalarNode('reminder_time')
                    ->defaultValue(15)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
