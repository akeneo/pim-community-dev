<?php

namespace Pim\Bundle\ReferenceDataBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pim_reference_data');

        $rootNode
            ->prototype('array')
                ->children()
                    ->scalarNode('class')->isRequired()->canNotBeEmpty()->end()
                    ->enumNode('type')->isRequired()->values(['simple', 'multi'])->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
