<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource as PimOrmDatasource;

/**
 * Orm pager extension
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmPagerExtension extends AbstractPagerExtension
{
    /**
     * @param DatagridConfiguration $config
     *
     * @return boolean
     */
    protected function matchDatasource(DatagridConfiguration $config)
    {
        $datasourceType = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH);

        return ($datasourceType == PimOrmDatasource::TYPE);
    }
}
