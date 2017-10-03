<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;

/**
 * Grid listener to configure the columns of attribute grid
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ConfigureAttributeGridListener
{
    /**
     * {@inheritdoc}
     */
    public function buildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();

        $this->configureColumns($config);
        $this->configureFilters($config);
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function configureColumns(DatagridConfiguration $config)
    {
        $column = [
            'smart' => [
                'label'         => 'pimee_catalog_rule.attribute.grid.is_smart.label',
                'frontend_type' => 'boolean-status',
                'data_name'     => 'is_smart'
            ]
        ];

        $config->offsetAddToArrayByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY), $column);
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function configureFilters(DatagridConfiguration $config)
    {
        $filter = [
            'smart' => [
                'type'      => 'attribute_is_smart',
                'data_name' => 'is_smart'
            ]
        ];

        $config->offsetAddToArrayByPath(FilterConfiguration::COLUMNS_PATH, $filter);
    }
}
