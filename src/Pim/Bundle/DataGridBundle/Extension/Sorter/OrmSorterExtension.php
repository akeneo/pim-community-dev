<?php

namespace Pim\Bundle\DataGridBundle\Extension\Sorter;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource as PimOrmDatasource;

/**
 * ORM sorter extension
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmSorterExtension extends AbstractSorterExtension
{
    /**
     * {@inheritdoc}
     */
    protected function matchDatasource(DatagridConfiguration $config)
    {
        $datasourceType = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH);

        return PimOrmDatasource::TYPE === $datasourceType;
    }
}
