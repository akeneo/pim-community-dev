<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CriteriaConditionSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->beConstructedWith($qb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition');
    }

    function it_throws_an_exception_when_the_value_is_invalid_for_the_operator()
    {
        $operators = array('=', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE');
        foreach ($operators as $operator) {
            $this
                ->shouldThrow('\InvalidArgumentException')
                ->duringPrepareCriteriaCondition('my_field', $operator, ['my_value1', 'my_value2'])
            ;
        }

        $operators = array('BETWEEN', 'IN', 'NOT IN');
        foreach ($operators as $operator) {
            $this
                ->shouldThrow('\InvalidArgumentException')
                ->duringPrepareCriteriaCondition('my_field', $operator, 'my_value')
            ;
        }
    }

    function it_throws_an_exception_when_the_operator_is_not_supported()
    {
        $this
            ->shouldThrow('\Pim\Bundle\CatalogBundle\Exception\ProductQueryException')
            ->duringPrepareCriteriaCondition('my_field', 'NOT SUPPORTED', Argument::any())
        ;
    }

    function it_processes_an_equal_criteria($qb, Expr $expr, Expr\Comparison $comp, Expr\Literal $literal)
    {
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->literal('my_value')->shouldBeCalled()->willReturn($literal);
        $expr->eq('my_field', $literal)->shouldBeCalled()->willReturn($comp);

        $this->prepareCriteriaCondition('my_field', '=', 'my_value');
    }

    function it_processes_a_less_than_criteria($qb, Expr $expr, Expr\Comparison $comp, Expr\Literal $literal)
    {
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->literal('my_value')->shouldBeCalled()->willReturn($literal);
        $expr->lt('my_field', $literal)->shouldBeCalled()->willReturn($comp);

        $this->prepareCriteriaCondition('my_field', '<', 'my_value');
    }

    function it_processes_a_less_than_or_equal_criteria($qb, Expr $expr, Expr\Comparison $comp, Expr\Literal $literal)
    {
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->literal('my_value')->shouldBeCalled()->willReturn($literal);
        $expr->lte('my_field', $literal)->shouldBeCalled()->willReturn($comp);

        $this->prepareCriteriaCondition('my_field', '<=', 'my_value');
    }

    function it_processes_a_greater_than_criteria($qb, Expr $expr, Expr\Comparison $comp, Expr\Literal $literal)
    {
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->literal('my_value')->shouldBeCalled()->willReturn($literal);
        $expr->gt('my_field', $literal)->shouldBeCalled()->willReturn($comp);

        $this->prepareCriteriaCondition('my_field', '>', 'my_value');
    }

    function it_processes_a_greater_than_or_equal_criteria($qb, Expr $expr, Expr\Comparison $comp, Expr\Literal $literal)
    {
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->literal('my_value')->shouldBeCalled()->willReturn($literal);
        $expr->gte('my_field', $literal)->shouldBeCalled()->willReturn($comp);

        $this->prepareCriteriaCondition('my_field', '>=', 'my_value');
    }

    function it_processes_a_like_criteria($qb, Expr $expr, Expr\Comparison $comp, Expr\Literal $literal)
    {
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->literal('my_value')->shouldBeCalled()->willReturn($literal);
        $expr->like('my_field', $literal)->shouldBeCalled()->willReturn($comp);

        $this->prepareCriteriaCondition('my_field', 'LIKE', 'my_value');
    }

    function it_processes_a_null_criteria($qb, Expr $expr)
    {
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->isNull('my_field')->shouldBeCalled();

        $this->prepareCriteriaCondition('my_field', 'NULL', Argument::any());
    }

    function it_processes_a_not_null_criteria($qb, Expr $expr)
    {
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->isNotNull('my_field')->shouldBeCalled();

        $this->prepareCriteriaCondition('my_field', 'NOT NULL', Argument::any());
    }

    function it_processes_an_in_criteria($qb, Expr $expr, Expr\Func $func)
    {
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->in('my_field', ['my_value1', 'my_value2'])->shouldBeCalled()->willReturn($func);

        $this->prepareCriteriaCondition('my_field', 'IN', ['my_value1', 'my_value2']);
    }

    function it_processes_a_not_in_criteria($qb, Expr $expr, Expr\Func $func)
    {
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->notIn('my_field', ['my_value1', 'my_value2'])->shouldBeCalled()->willReturn($func);

        $this->prepareCriteriaCondition('my_field', 'NOT IN', ['my_value1', 'my_value2']);
    }

    function it_processes_a_not_like_criteria($qb, Expr $expr, Expr\Literal $literal)
    {
        $literal->__toString()->willReturn('');
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->literal('my_value')->shouldBeCalled()->willReturn($literal);

        $this->prepareCriteriaCondition('my_field', 'NOT LIKE', 'my_value');
    }

    function it_processes_a_between_criteria($qb, Expr $expr, Expr\Literal $literal)
    {
        $literal->__toString()->shouldBeCalledTimes(2)->willReturn('');
        $qb->expr()->shouldBeCalledTimes(2)->willReturn($expr);
        $expr->literal('my_value1')->shouldBeCalled()->willReturn($literal);
        $expr->literal('my_value2')->shouldBeCalled()->willReturn($literal);

        $this->prepareCriteriaCondition('my_field', 'BETWEEN', ['my_value1', 'my_value2']);
    }

    function it_processes_an_empty_criteria($qb, Expr $expr)
    {
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->isNull('my_field')->shouldBeCalled();

        $this->prepareCriteriaCondition('my_field', 'EMPTY', Argument::any());
    }
}
