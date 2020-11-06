<?php

namespace Oro\Bundle\FilterBundle\Datasource\Orm;

use Doctrine\ORM\Query\Expr\Comparison;
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
    public function comparison($x, string $operator, $y, bool $withParam = false): Comparison
    {
        return new Comparison($x, $operator, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function eq($x, $y, bool $withParam = false)
    {
        return $this->expr->eq($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function neq($x, $y, bool $withParam = false)
    {
        return $this->expr->neq($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function lt($x, $y, bool $withParam = false)
    {
        return $this->expr->lt($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function lte($x, $y, bool $withParam = false)
    {
        return $this->expr->lte($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function gt($x, $y, bool $withParam = false)
    {
        return $this->expr->gt($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function gte($x, $y, bool $withParam = false)
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
    public function in(string $x, $y, bool $withParam = false)
    {
        return $this->expr->in($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function notIn(string $x, $y, bool $withParam = false)
    {
        return $this->expr->notIn($x, $withParam ? ':' . $y : $y);
    }

    /**
     * {@inheritdoc}
     */
    public function isNull(string $x)
    {
        return $this->expr->isNull($x);
    }

    /**
     * {@inheritdoc}
     */
    public function isNotNull(string $x)
    {
        return $this->expr->isNotNull($x);
    }

    /**
     * {@inheritdoc}
     */
    public function like(string $x, $y, bool $withParam = false)
    {
        return $this->expr->like($x, $withParam ? ':' . $y : $y);
    }
}
