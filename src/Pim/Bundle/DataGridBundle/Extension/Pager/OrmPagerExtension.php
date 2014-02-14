<?php

namespace Pim\Bundle\DataGridBundle\Extension\Pager;

use Doctrine\ORM\AbstractQuery;
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
        $results = $cloneQb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
        $ids = array_keys($results);

        // update query selection
        if (count($ids) > 0) {

            $attributeIds = array(1, 5, 18, 29, 32, 36, 37, 45, 51, 55, 59, 1305, 1306, 1307);

            $datasource->getQueryBuilder()

                ->leftJoin(
                    'p.values',
                    'values',
                    'WITH',
                    'values.attribute IN (:attributeIds) '
                    .'AND (values.locale = :dataLocale OR values.locale IS NULL) '
                    .'AND (values.scope = :scopeCode OR values.scope IS NULL)'
                )
                ->leftJoin('values.attribute', 'attribute')
                ->addSelect('values')
                ->addSelect('attribute')

                ->leftJoin(
                    'values.prices',
                    'prices',
                    '(prices.locale = :dataLocale OR prices.locale IS NULL) '
                    .'AND (prices.scope = :scopeCode OR prices.scope IS NULL)'
                )
                ->addSelect('prices')

                ->leftJoin(
                    'values.option',
                    'option',
                    '(option.locale = :dataLocale OR option.locale IS NULL) '
                    .'AND (option.scope = :scopeCode OR option.scope IS NULL)'
                )
                ->addSelect('option')
                ->leftJoin(
                    'option.optionValues',
                    'optionValues',
                    'optionValues.locale = :dataLocale'
                )
                ->addSelect('optionValues')

                ->leftJoin(
                    'values.options',
                    'options',
                    '(options.locale = :dataLocale OR options.locale IS NULL) '
                    .'AND (options.scope = :scopeCode OR options.scope IS NULL)'
                )
                ->addSelect('options')
                ->leftJoin(
                    'options.optionValues',
                    'optionsValues',
                    'optionsValues.locale = :dataLocale'
                )
                ->addSelect('optionsValues')

                ->andWhere($rootField.' IN (:entityIds)')
                ->setParameter('entityIds', $ids)
                ->setParameter('attributeIds', $attributeIds);
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
