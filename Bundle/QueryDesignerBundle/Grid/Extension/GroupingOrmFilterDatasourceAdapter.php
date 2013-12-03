<?php

namespace Oro\Bundle\QueryDesignerBundle\Grid\Extension;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Oro\Bundle\FilterBundle\Datasource\Orm\OrmFilterDatasourceAdapter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;

/**
 * Represents ORM data source adapter which allows to combine restrictions in groups,
 * thus it allows to specify priority of restrictions
 */
class GroupingOrmFilterDatasourceAdapter extends OrmFilterDatasourceAdapter
{
    /**
     * @var Expr\Composite[]
     */
    protected $exprStack;

    /**
     * @var string[]
     */
    protected $conditionStack;

    /**
     * @var Expr\Composite
     */
    protected $currentExpr = null;

    /**
     * @var string
     */
    protected $currentCondition = null;

    /**
     * Constructor
     *
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb)
    {
        parent::__construct($qb);
        $this->resetState();
    }

    /**
     * {@inheritdoc}
     */
    public function addRestriction($restriction, $condition, $isComputed = false)
    {
        if ($isComputed) {
            throw new \LogicException('The HAVING restrictions is not supported yet.');
        }

        if ($this->currentExpr === null) {
            // this is the first item in a group
            $this->currentExpr = $restriction;
        } else {
            // other items
            if ($condition === FilterUtility::CONDITION_OR) {
                if ($this->currentExpr instanceof Expr\Orx) {
                    $this->currentExpr->add($restriction);
                } else {
                    $this->currentExpr = $this->qb->expr()->orX($this->currentExpr, $restriction);
                }
            } else {
                if ($this->currentExpr instanceof Expr\Andx) {
                    $this->currentExpr->add($restriction);
                } else {
                    $this->currentExpr = $this->qb->expr()->andX($this->currentExpr, $restriction);
                }
            }
        }
    }

    public function beginRestrictionGroup($condition)
    {
        array_push($this->exprStack, $this->currentExpr);
        array_push($this->conditionStack, $this->currentCondition);

        $this->currentExpr      = null;
        $this->currentCondition = $condition;
    }

    public function endRestrictionGroup()
    {
        $tmpExpr                = $this->currentExpr;
        $tmpCondition           = $this->currentCondition;
        $this->currentExpr      = array_pop($this->exprStack);
        $this->currentCondition = array_pop($this->conditionStack);

        $this->addRestriction($tmpExpr, $tmpCondition);
    }

    /**
     * Applies all restrictions previously added using addRestriction and addRestrictionOperator methods
     */
    public function applyRestrictions()
    {
        if ($this->currentExpr === null) {
            $this->currentExpr = array_pop($this->exprStack);
        }
        $this->qb->andWhere($this->currentExpr);
        $this->resetState();
    }

    /**
     * Resets all 'state' variables of this adapter
     */
    protected function resetState()
    {
        $this->exprStack        = [];
        $this->conditionStack   = [];
        $this->currentExpr      = null;
        $this->currentCondition = null;
    }
}
