<?php

namespace Pim\Bundle\FilterBundle\Datasource\Odm;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

/**
 * Odm datasource adapter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OdmFilterDatasourceAdapter implements FilterDatasourceAdapterInterface
{
    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var OrmExpressionBuilder
     */
    protected $expressionBuilder;

    /**
     * Constructor
     *
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb)
    {
        $this->qb                = $qb;
        $this->expressionBuilder = null;
    }

    /**
     * Adds a new WHERE or HAVING restriction depends on the given parameters.
     *
     * @param mixed  $restriction The restriction to add.
     * @param string $condition   The condition.
     *                            Can be FilterUtility::CONDITION_OR or FilterUtility::CONDITION_AND.
     * @param bool   $isComputed  Specifies whether the restriction should be added to the HAVING part of a query.
     */
    public function addRestriction($restriction, $condition, $isComputed = false)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function groupBy($_)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function addGroupBy($_)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function expr()
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($key, $value, $type = null)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function generateParameterName($filterName)
    {
        // @TODO throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }

    /**
     * Returns a QueryBuilder object used to modify this data source
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }
}
