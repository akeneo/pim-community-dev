<?php

namespace Pim\Bundle\DataGridBundle\Extension\Selector;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\From;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface as OroDatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\QueryBuilderUtility;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;

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
     * @var string
     */
    const COLUMN_SELECTOR_PATH = 'selector';

    /**
     * @var string
     */
    protected $storageDriver;

    /**
     * @var SelectorInterface[]
     */
    protected $selectors;

    /**
     * @var string[]
     */
    protected $eligibleDatasource = [];

    /**
     * Constructor
     *
     * @param string            $storageDriver
     * @param RequestParameters $requestParams
     */
    public function __construct($storageDriver, RequestParameters $requestParams = null)
    {
        $this->storageDriver = $storageDriver;
        $this->requestParams = $requestParams;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $datasourceType = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH);

        if (in_array($datasourceType, $this->eligibleDatasource) &&
            AkeneoStorageUtilsExtension::DOCTRINE_ORM === $this->storageDriver) {
            return true;
        }

        return false;
    }

    /**
     * Add selector to array of available selectors
     *
     * @param string            $name
     * @param SelectorInterface $selector
     *
     * @return $this
     */
    public function addSelector($name, SelectorInterface $selector)
    {
        $this->selectors[$name] = $selector;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, OroDatasourceInterface $datasource)
    {
        $entityIds = $this->getEntityIds($datasource);
        $rootAlias = $datasource->getQueryBuilder()->getRootAlias();
        $rootField = $rootAlias.'.id';

        if (count($entityIds) > 0) {
            $datasource->getQueryBuilder()
                ->andWhere($rootField.' IN (:entityIds)')->setParameter('entityIds', $entityIds);

            $datasource->getQueryBuilder()->setFirstResult(null)->setMaxResults(null);
        }

        $selectors = $this->getSelectorsToApply($config);
        foreach ($selectors as $selector) {
            $selector->apply($datasource, $config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return -400;
    }

    /**
     * @param string $datasource
     *
     * @return OrmSelectorExtension
     */
    public function addEligibleDatasource($datasource)
    {
        $this->eligibleDatasource[] = $datasource;

        return $this;
    }

    /**
     * Prepare selectors array
     *
     * @param DatagridConfiguration $config
     *
     * @return SelectorInterface[]
     */
    protected function getSelectorsToApply(DatagridConfiguration $config)
    {
        $selectors = [];
        $columnsConfig = $config->offsetGetByPath(
            sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)
        );

        foreach ($columnsConfig as $column) {
            if (isset($column[self::COLUMN_SELECTOR_PATH]) && $name = $column[self::COLUMN_SELECTOR_PATH]) {
                $selectors[$name] = $this->selectors[$name];
            }
        }

        return $selectors;
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
        $getIdsQb = clone $datasource->getQueryBuilder();
        $rootEntity = current($getIdsQb->getRootEntities());
        $rootAlias = $getIdsQb->getRootAlias();
        $rootField = $rootAlias.'.id';
        $getIdsQb->add('from', new From($rootEntity, $rootAlias, $rootField), false);
        $getIdsQb->groupBy($rootField);
        QueryBuilderUtility::removeExtraParameters($getIdsQb);
        $results = $getIdsQb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        return array_keys($results);
    }
}
