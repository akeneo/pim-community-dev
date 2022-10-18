<?php

namespace Oro\Bundle\FilterBundle\Datasource\Orm;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;

/**
 * Represents an adapter to ORM data source
 */
class OrmFilterDatasourceAdapter implements FilterDatasourceAdapterInterface
{
    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @var OrmExpressionBuilder
     */
    private $expressionBuilder;

    /**
     * Constructor
     *
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
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
        if ($this->fixComparison($restriction, $condition)) {
            return;
        }

        if ($condition === FilterUtility::CONDITION_OR) {
            if ($isComputed) {
                $this->qb->orHaving($restriction);
            } else {
                $this->qb->orWhere($restriction);
            }
        } else {
            if ($isComputed) {
                $this->qb->andHaving($restriction);
            } else {
                $this->qb->andWhere($restriction);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function groupBy($_)
    {
        return call_user_func_array([$this->qb, 'groupBy'], func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function addGroupBy($_)
    {
        return call_user_func_array([$this->qb, 'addGroupBy'], func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function expr()
    {
        if ($this->expressionBuilder === null) {
            $this->expressionBuilder = new OrmExpressionBuilder($this->qb->expr());
        }

        return $this->expressionBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($key, $value, $type = null)
    {
        $this->qb->setParameter($key, $value, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function generateParameterName($filterName)
    {
        return preg_replace('#[^a-z0-9]#i', '', $filterName) . mt_rand();
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

    /**
     * Note: this is workaround for http://www.doctrine-project.org/jira/browse/DDC-1858
     * It could be removed when doctrine version >= 2.4
     *
     * @param mixed  $restriction The restriction to check.
     * @param string $condition   The condition.
     *                            Can be FilterUtility::CONDITION_OR or FilterUtility::CONDITION_AND.
     * @return bool true if a the given restriction was fixed and applied to the query builder; otherwise, false.
     */
    protected function fixComparison($restriction, $condition)
    {
        if ($restriction instanceof Expr\Comparison
            && ($restriction->getOperator() === 'LIKE' || $restriction->getOperator() === 'NOT LIKE')
        ) {
            return $this->tryApplyWhereRestriction($restriction, $condition);
        }

        return false;
    }

    /**
     * Applies the given restriction to the WHERE part of the query
     *
     * @param mixed  $restriction The restriction to check.
     * @param string $condition   The condition.
     *                            Can be FilterUtility::CONDITION_OR or FilterUtility::CONDITION_AND.
     * @return bool true if a the given restriction was applied to the query builder; otherwise, false.
     */
    protected function tryApplyWhereRestriction($restriction, $condition)
    {
        if (!($restriction instanceof Expr\Comparison)) {
            return false;
        }

        $expectedAlias = (string)$restriction->getLeftExpr();

        $extraSelect = null;
        foreach ($this->qb->getDQLPart('select') as $selectPart) {
            foreach ($selectPart->getParts() as $part) {
                if (preg_match("#(.*)\\s+as\\s+" . preg_quote($expectedAlias) . "#i", $part, $matches)) {
                    $extraSelect = $matches[1];
                    break;
                }
            }
        }
        if ($extraSelect === null) {
            return false;
        }

        $restriction = new Expr\Comparison(
            $extraSelect,
            $restriction->getOperator(),
            $restriction->getRightExpr()
        );
        $this->addRestriction($restriction, $condition);

        return true;
    }
}
