<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\Operators;
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
        $operators = ['=', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE'];
        foreach ($operators as $operator) {
            $this
                ->shouldThrow(
                    InvalidOperatorException::scalarExpected(
                        [
                            Operators::EQUALS                => 'eq',
                            Operators::NOT_EQUAL             => 'neq',
                            Operators::LOWER_THAN            => 'lt',
                            Operators::LOWER_OR_EQUAL_THAN   => 'lte',
                            Operators::GREATER_THAN          => 'gt',
                            Operators::GREATER_OR_EQUAL_THAN => 'gte',
                            Operators::IS_LIKE               => 'like',
                            Operators::IS_NOT_LIKE           => 'notLike'
                        ],
                        'Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition',
                        ['my_value1', 'my_value2']
                    )
                )
                ->duringPrepareCriteriaCondition('my_field', $operator, ['my_value1', 'my_value2'])
            ;
        }

        $operators = ['IN', 'NOT IN'];
        foreach ($operators as $operator) {
            $this
                ->shouldThrow(
                    InvalidOperatorException::arrayExpected(
                        [Operators::IN_LIST => 'in', Operators::NOT_IN_LIST => 'notIn'],
                        'Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition',
                        'my_value'
                    )
                )
                ->duringPrepareCriteriaCondition('my_field', $operator, 'my_value')
            ;
        }

        $this
            ->shouldThrow(
                InvalidOperatorException::arrayExpected(
                    ['BETWEEN'],
                    'Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition',
                    'my_value'
                )
            )
            ->duringPrepareCriteriaCondition('my_field', 'BETWEEN', 'my_value')
        ;
    }

    function it_throws_an_exception_when_the_operator_is_not_supported()
    {
        $this
            ->shouldThrow(
                InvalidOperatorException::notSupported(
                    'NOT SUPPORTED',
                    'Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition'
                )
            )
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

    function it_processes_a_not_like_criteria($qb, Expr $expr, Expr\Comparison $comp, Expr\Literal $literal)
    {
        $qb->expr()->shouldBeCalled()->willReturn($expr);
        $expr->literal('my_value')->shouldBeCalled()->willReturn($literal);
        $expr->notLike('my_field', $literal)->shouldBeCalled()->willReturn($comp);

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

    function it_processes_a_not_empty_criteria($qb, Expr $expr, Expr\Comparison $comp, Expr\Literal $literal)
    {
        $qb->expr()->willReturn($expr);
        $expr->isNotNull('my_field')->shouldBeCalled()->willReturn('my_field IS NOT NULL');

        $this
            ->prepareCriteriaCondition('my_field', 'NOT EMPTY', '')
            ->shouldReturn('my_field IS NOT NULL');
    }
}
