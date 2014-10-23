<?php

namespace Pim\Bundle\FilterBundle\Datasource\Orm;

use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Datasource\Orm\OrmFilterDatasourceAdapter as OroOrmFilterDatasourceAdapter;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

/**
 * Customize the OroPlatform datasource adapter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmFilterDatasourceAdapter extends OroOrmFilterDatasourceAdapter implements
    FilterDatasourceAdapterInterface
{
    /**
     * Constructor
     *
     * @param DatasourceInterface $datasource
     */
    public function __construct(DatasourceInterface $datasource)
    {
        $this->qb  = $datasource->getQueryBuilder();
        $this->expressionBuilder = null;
    }
}
