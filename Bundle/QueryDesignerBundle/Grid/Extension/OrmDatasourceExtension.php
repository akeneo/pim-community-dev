<?php

namespace Oro\Bundle\QueryDesignerBundle\Grid\Extension;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr as Expr;

use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsObject;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\QueryDesignerBundle\QueryDesigner\Manager;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;

class OrmDatasourceExtension implements ExtensionVisitorInterface
{
    /** @var Manager */
    protected $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH) == OrmDatasource::TYPE
        && $config->offsetGetByPath('[source][query_config][filters]');
    }

    /**
     * {@inheritdoc}
     */
    public function processConfigs(DatagridConfiguration $config)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        /** @var QueryBuilder $qb */
        $qb      = $datasource->getQueryBuilder();
        $ds      = new GroupingOrmFilterDatasourceAdapter($qb);
        $filters = $config->offsetGetByPath('[source][query_config][filters]');
        $this->buildRestrictions($filters, $ds);
        $ds->applyRestrictions();
    }

    /**
     * {@inheritdoc}
     */
    public function visitResult(DatagridConfiguration $config, ResultsObject $result)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataObject $data)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return 0;
    }

    /**
     * Recursive iterates through filters and builds an expression to be applied to the given data source
     *
     * @param array                              $filters
     * @param GroupingOrmFilterDatasourceAdapter $ds
     */
    protected function buildRestrictions(array &$filters, GroupingOrmFilterDatasourceAdapter $ds)
    {
        $operatorStack = [FilterUtility::CONDITION_AND];
        foreach ($filters as $item) {
            if (is_string($item)) {
                array_push($operatorStack, $item);
            } elseif (!isset($item['filter'])) {
                $ds->beginRestrictionGroup(array_pop($operatorStack));
                $this->buildRestrictions($item, $ds);
                $ds->endRestrictionGroup();
            } else {
                $operator = array_pop($operatorStack);
                /** @var FilterInterface $filter */
                $filter = $this->getFilterObject($item['filter'], $item['column']);
                $form   = $filter->getForm();
                if (!$form->isSubmitted()) {
                    $form->submit($item['filterData']);
                }
                if ($form->isValid()) {
                    $ds->beginRestrictionGroup($operator);
                    $filter->apply($ds, $form->getData());
                    $ds->endRestrictionGroup();
                }
            }
        }
    }

    /**
     * Returns prepared filter object.
     *
     * @param string $name       A filter name.
     * @param string $columnName A column name this filter should be applied.
     * @param string $operator   A filter operator. Can be "OR" or "AND".
     * @return FilterInterface
     */
    protected function getFilterObject($name, $columnName, $operator = null)
    {
        $params = [
            FilterUtility::DATA_NAME_KEY => $columnName
        ];
        if ($operator !== null) {
            $params[FilterUtility::CONDITION_KEY] = $operator;
        }

        return $this->manager->createFilter($name, $params);
    }
}
