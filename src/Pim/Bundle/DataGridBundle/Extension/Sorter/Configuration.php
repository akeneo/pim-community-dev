<?php

namespace Pim\Bundle\DataGridBundle\Extension\Sorter;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OroConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;

/**
 * Sorter configuration, extended to add own configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Configuration extends OroConfiguration
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('sorters')
            ->children()
                ->arrayNode('columns')
                    ->prototype('array')
                        ->children()
                            ->scalarNode(PropertyInterface::DATA_NAME_KEY)->isRequired()->end()
                            ->variableNode('apply_callback')->end()
                            ->variableNode('sorter')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('default')
                    ->prototype('enum')
                        ->values([OrmSorterExtension::DIRECTION_DESC, OrmSorterExtension::DIRECTION_ASC])->end()
                    ->end()
                    ->booleanNode('multiple_sorting')->end()
                ->end()
            ->end();

        return $builder;
    }
}
