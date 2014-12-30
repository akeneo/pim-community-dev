<?php

namespace Akeneo\Bundle\StorageUtilsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Akeneo storage utils bundle configuration
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('akeneo_storage_utils');

        $rootNode
            ->children()
                ->scalarNode('storage_driver')->defaultValue('doctrine/orm')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
