<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Pim product bundle configuration
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
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
        $rootNode = $treeBuilder->root('pim_product');

        $rootNode
            ->children()
                ->booleanNode('record_mails')->defaultFalse()->end()
                ->scalarNode('imported_product_data_transformer')->defaultNull()->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
