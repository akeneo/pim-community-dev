<?php

namespace Pim\Bundle\EnrichBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Pim Enrich bundle configuration
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
        $rootNode = $treeBuilder->root('pim_enrich');

        $rootNode
            ->children()
                ->booleanNode('record_mails')->defaultFalse()->end()
                ->scalarNode('max_products_category_removal')->defaultValue('100')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
