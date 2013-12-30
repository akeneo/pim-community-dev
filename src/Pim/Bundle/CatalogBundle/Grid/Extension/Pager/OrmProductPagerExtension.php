<?php

namespace Pim\Bundle\CatalogBundle\Grid\Extension\Pager;

use Oro\Bundle\DataGridBundle\Extension\Pager\OrmPagerExtension as OroOrmPagerExtension;
use Oro\Bundle\DataGridBundle\Extension\Pager\Configuration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\Toolbar\ToolbarExtension;
use Pim\Bundle\CatalogBundle\Datasource\Orm\OrmProductDatasource;

/**
 * Orm product pager extension
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmProductPagerExtension extends OroOrmPagerExtension
{
    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH) == OrmProductDatasource::TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $defaultPerPage = $config->offsetGetByPath(ToolbarExtension::PAGER_DEFAULT_PER_PAGE_OPTION_PATH, 10);

        // override to reset left join select and fix paging results
        $qb = clone $datasource->getQueryBuilder();
        $qb->select('p');
        $this->pager->setQueryBuilder($qb);

        $this->pager->setPage($this->getOr(self::PAGE_PARAM, 1));
        $this->pager->setMaxPerPage($this->getOr(self::PER_PAGE_PARAM, $defaultPerPage));
        $this->pager->init();
    }
}
