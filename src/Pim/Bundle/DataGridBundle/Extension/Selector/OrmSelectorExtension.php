<?php

namespace Pim\Bundle\DataGridBundle\Extension\Selector;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\From;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource as PimOrmDatasource;

/**
 * Orm selector extension
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmSelectorExtension extends AbstractExtension
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
     * Retrieve entity ids, filters, sorters and limits are already in the datasource query builder
     *
     * @param DatasourceInterface $datasource
     *
     * @return array
     */
    protected function getEntityIds(DatasourceInterface $datasource)
    {
        $getIdsQb   = clone $datasource->getQueryBuilder();
        $rootEntity = current($getIdsQb->getRootEntities());
        $rootAlias  = $getIdsQb->getRootAlias();
        $rootField  = $rootAlias.'.id';
        $getIdsQb->add('from', new From($rootEntity, $rootAlias, $rootField), false);
        $results = $getIdsQb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        return array_keys($results);
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        // TODO : check no result
        // TODO: check filters
        $entityIds = $this->getEntityIds($datasource);
        $rootAlias = $datasource->getQueryBuilder()->getRootAlias();
        $rootField = $rootAlias.'.id';

        // filter by entity ids and reset limits
        if (count($entityIds) > 0) {
            $datasource->getQueryBuilder()
                ->andWhere($rootField.' IN (:entityIds)')->setParameter('entityIds', $entityIds);

            $datasource->getQueryBuilder()->setFirstResult(null)->setMaxResults(null);
        }

        $isFlexible = $config->offsetGetByPath('[source][is_flexible]');
        if ($isFlexible) {

            //$attributeIds = array(1, 5, 18, 29, 32, 36, 37, 45, 51, 55, 59, 1305, 1306, 1307);
            $attributeIds = array(1, 1142, 1051, 359, 55, 586);

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
                ->setParameter('entityIds', $entityIds)
                ->setParameter('attributeIds', $attributeIds);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return -400;
    }
}
