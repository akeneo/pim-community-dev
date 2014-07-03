<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;

/**
 * Product pager extension, this one is storage agnostic, the injected pager depends on product storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPagerExtension extends AbstractPagerExtension
{
    /**
     * @param DatagridConfiguration $config
     *
     * @return boolean
     */
    protected function matchDatasource(DatagridConfiguration $config)
    {
        $datasourceType = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH);

        return ProductDatasource::TYPE === $datasourceType;
    }
}
