<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\Pager\OrmPagerExtension as OroOrmPagerExtension;
use Oro\Bundle\DataGridBundle\Extension\Toolbar\ToolbarExtension;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource as PimOrmDatasource;

/**
 * Orm pager extension
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmPagerExtension extends OroOrmPagerExtension
{
    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return $this->matchDatasource($config);
    }

    /**
     * @param DatagridConfiguration $config
     *
     * @return boolean
     */
    protected function matchDatasource(DatagridConfiguration $config)
    {
        return $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH) == PimOrmDatasource::TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $defaultPerPage = $config->offsetGetByPath(ToolbarExtension::PAGER_DEFAULT_PER_PAGE_OPTION_PATH, 10);

        // override to fix paging results
        $cloneQb = clone $datasource->getQueryBuilder();

        // prepare query to get entity ids
        $rootEntity = current($cloneQb->getRootEntities());
        $rootAlias = $cloneQb->getRootAlias();
        $rootField = $rootAlias.'.id';
        $cloneQb->add(
            'from',
            new \Doctrine\ORM\Query\Expr\From($rootEntity, $rootAlias, $rootField),
            false
        );
        $cloneQb->groupBy($rootField);

        // configure pager
        $this->pager->setQueryBuilder($cloneQb);
        $this->pager->setPage($this->getOr(self::PAGE_PARAM, 1));
        $this->pager->setMaxPerPage($this->getOr(self::PER_PAGE_PARAM, $defaultPerPage));
        $this->pager->init();

        // get entity ids
        $results = $cloneQb->getQuery()
            ->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $ids = array_keys($results);

        // update query selection
        if (count($ids) > 0) {
            $datasource->getQueryBuilder()
                ->andWhere($rootField.' IN (:entityIds)')
                ->setParameter('entityIds', $ids);
        }
    }

    /**
     * Should pass at the very end (after filters and sorters)
     *
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return -300;
    }
}
