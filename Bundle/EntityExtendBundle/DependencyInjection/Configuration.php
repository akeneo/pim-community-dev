<?php

namespace Oro\Bundle\EntityExtendBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('oro_entity_extend')
            ->children()
                ->scalarNode('backend')
                    ->isRequired()
                    ->validate()
                        ->ifNotInArray(array('Dynamic', 'EAV'))
                        ->thenInvalid('Invalid mode value "%s"')
                    ->end()
                ->end()
                ->scalarNode('cache_dir')->cannotBeEmpty()->defaultValue('%kernel.cache_dir%/oro_extend')->end()
                #->scalarNode('extend_namespace')->cannotBeEmpty()->defaultValue('Extend\Entity')->end()
            ->end()
            ->children()
                ->scalarNode('backup')->cannotBeEmpty()->defaultValue('%kernel.root_dir%/entities/Backup')->end()
            ->end();

        return $treeBuilder;
    }
}
