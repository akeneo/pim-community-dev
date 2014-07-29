<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Pim\Bundle\DataGridBundle\Datagrid\Product\FiltersConfigurator as BaseFiltersConfigurator;
use PimEnterprise\Bundle\FilterBundle\Filter\Product\PermissionFilter;

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
            'type'      => 'product_permission',
            'ftype'     => 'choice',
            'data_name' => 'permissions',
            'label'     => 'pimee_workflow.product.permission.label',
            'options'   => [
                'field_options' => [
                    'multiple' => false,
                    'choices'  => [
                        PermissionFilter::OWN => 'pimee_workflow.product.permission.own',
                        PermissionFilter::EDIT => 'pimee_workflow.product.permission.edit',
                        PermissionFilter::VIEW => 'pimee_workflow.product.permission.view',
                    ]
                ]
            ]
        ];
        $this->configuration->offsetSetByPath(
            sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, 'permission'),
            $filter
        );
    }
}
