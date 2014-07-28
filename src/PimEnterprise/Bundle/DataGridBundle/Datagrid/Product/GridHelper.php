<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\Product;

/**
 * Helper for product datagrid
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class GridHelper
{
    /**
     * Returns available is owner choices
     *
     * @return array
     */
    public function getOwnerChoices()
    {
        return [
            1 => 'pimee_workflow.product.is_owner.yes',
            0 => 'pimee_workflow.product.is_owner.no',
        ];
    }
}
