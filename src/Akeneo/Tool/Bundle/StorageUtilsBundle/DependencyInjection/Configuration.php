<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\DependencyInjection;

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
                ->arrayNode('mapping_overrides')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('original')->isRequired()->cannotBeEmpty()
                                ->validate()
                                ->ifTrue(
                                    function ($class) {
                                        return false === class_exists($class);
                                    }
                                )
                                ->thenInvalid('Invalid original class "%s".')
                                ->end()
                            ->end()
                            ->scalarNode('override')->isRequired()->cannotBeEmpty()
                                ->validate()
                                ->ifTrue(
                                    function ($class) {
                                        return false === class_exists($class);
                                    }
                                )
                                ->thenInvalid('Invalid overriden class "%s".')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
