<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('pim_import_export');

        $rootNode
            ->children()
                ->arrayNode('encoders')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('csv')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('delimiter')->defaultValue(';')->end()
                                ->scalarNode('enclosure')->defaultValue('"')->end()
                                ->scalarNode('with_header')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
