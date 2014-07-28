<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

use Pim\Bundle\DataGridBundle\Datagrid\Product\FiltersConfigurator as BaseFiltersConfigurator;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;

/**
 * Override filters configurator to add is owner filter in product grid
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class FiltersConfigurator extends BaseFiltersConfigurator
{
    /**
     * {@inheritdoc}
     */
    public function configure(DatagridConfiguration $configuration)
    {
        parent::configure($configuration);
        $this->addIsOwnerFilter();
    }

    /**
     * Add the is owner filter in the datagrid configuration
     */
    protected function addIsOwnerFilter()
    {
        $filter = [
            'type'      => 'product_is_owner',
            'ftype'     => 'choice',
            'data_name' => 'is_owner',
            'label'     => 'pimee_workflow.product.is_owner.label',
            'options'   => [
                'field_options' => [
                    'multiple' => false,
                    'choices'  => [
                        1 => 'pimee_workflow.product.is_owner.yes',
                        0 => 'pimee_workflow.product.is_owner.no'
                    ]
                ]
            ]
        ];
        $this->configuration->offsetSetByPath(
            sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'is_owner'),
            $filter
        );
    }
}
