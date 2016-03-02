<?php

namespace Oro\Bundle\FilterBundle\Datasource\Orm;

use Doctrine\ORM\Query\Expr;
use Oro\Bundle\FilterBundle\Datasource\ExpressionBuilderInterface;

class OrmExpressionBuilder implements ExpressionBuilderInterface
{
    protected $expr;

    public function __construct(Expr $expr)
    {
        $this->expr = $expr;
    }

    /**
     * {@inheritdoc}
     */
    public function andX($_)
    {
        return call_user_func_array([$this->expr, 'andX'], func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function orX($_)
    {
        return call_user_func_array([$this->expr, 'orX'], func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function comparison($x, $operator, $y, $withParam = false)
    {
        return new Expr\Comparison($x, $operator, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function eq($x, $y, $withParam = false)
    {
        return $this->expr->eq($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function neq($x, $y, $withParam = false)
    {
        return $this->expr->neq($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function lt($x, $y, $withParam = false)
    {
        return $this->expr->lt($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function lte($x, $y, $withParam = false)
    {
        return $this->expr->lte($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function gt($x, $y, $withParam = false)
    {
        return $this->expr->gt($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function gte($x, $y, $withParam = false)
    {
        return $this->expr->gte($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function not($restriction)
    {
        return $this->expr->not($restriction);
    }

    /**
     * {@inheritdoc}
     */
    public function in($x, $y, $withParam = false)
    {
        return $this->expr->in($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function notIn($x, $y, $withParam = false)
    {
        return $this->expr->notIn($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function isNull($x)
    {
        return $this->expr->isNull($x);
    }

    /**
     * {@inheritdoc}
     */
    public function isNotNull($x)
    {
        return $this->expr->isNotNull($x);
    }

    /**
     * {@inheritdoc}
     */
    public function like($x, $y, $withParam = false)
    {
        return $this->expr->like($x, $withParam ? ':' . $y : $y);
    }
}
