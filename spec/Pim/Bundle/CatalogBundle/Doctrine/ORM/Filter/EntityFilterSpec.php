<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;

class EntityFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->beConstructedWith(['family', 'groups'], ['IN', 'NOT IN']);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'NOT IN']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_in_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->shouldBeCalled()->willReturn('f');
        $qb->leftJoin('f.family', 'filterfamily')->shouldBeCalled()->willReturn($qb);
        $qb->andWhere('filterfamily.id IN (1, 2)')->shouldBeCalled()->willReturn($qb);

        $expr->in('filterfamily.id', [1, 2])->shouldBeCalled()->willReturn('filterfamily.id IN (1, 2)');
        $qb->expr()->shouldBeCalled()->willReturn($expr);

        $this->addFieldFilter('family', 'IN', [1, 2]);
    }

    function it_adds_an_empty_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->shouldBeCalled()->willReturn('f');
        $qb->leftJoin('f.family', 'filterfamily')->shouldBeCalled()->willReturn($qb);
        $qb->andWhere('filterfamily.id IS NULL')->shouldBeCalled()->willReturn($qb);

        $expr->isNull('filterfamily.id')->shouldBeCalled()->willReturn('filterfamily.id IS NULL');
        $expr->in('filterfamily.id', [1 => 2])->shouldBeCalled()->willReturn('filterfamily.id IN (1, 2)');
        $expr->orX('filterfamily.id IS NULL', 'filterfamily.id IN (1, 2)')
            ->shouldBeCalled()
            ->willReturn('filterfamily.id IS NULL');
        $qb->expr()->shouldBeCalled()->willReturn($expr);

        $this->addFieldFilter('family', 'IN', ['empty', 2]);
    }

    function it_adds_an_empty_filter_with_no_values_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->shouldBeCalled()->willReturn('f');
        $qb->leftJoin('f.family', 'filterfamily')->shouldBeCalled()->willReturn($qb);
        $qb->andWhere('filterfamily.id IS NULL')->shouldBeCalled()->willReturn($qb);

        $expr->isNull('filterfamily.id')->shouldBeCalled()->willReturn('filterfamily.id IS NULL');
        $qb->expr()->shouldBeCalled()->willReturn($expr);

        $this->addFieldFilter('family', 'IN', ['empty']);
    }

    function it_adds_an_not_in_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->shouldBeCalled()->willReturn('f');
        $qb->leftJoin('f.family', 'filterfamily')->shouldBeCalled()->willReturn($qb);
        $qb->andWhere('filterfamily.id NOT IN(3)' . 'filterfamily.id IS NULL')->shouldBeCalled()->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $expr->notIn('filterfamily'.'.id', ['empty'])->shouldBeCalled()->willReturn('filterfamily.id NOT IN');
        $expr->isNull('filterfamily.id')->shouldBeCalled()->willReturn('filterfamily.id IS NULL');

        $expr->orX('filterfamily.id NOT IN', 'filterfamily.id IS NULL')
            ->shouldBeCalled()
            ->willReturn('filterfamily.id NOT IN(3)' . 'filterfamily.id IS NULL');

        $this->addFieldFilter('family', 'NOT IN', ['empty']);
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('family')->shouldReturn(true);
        $this->supportsField('groups')->shouldReturn(true);
        $this->supportsField('other')->shouldReturn(false);
    }

    function it_throws_an_exception_if_value_is_not_an_array()
    {
        $this->shouldThrow(InvalidArgumentException::arrayExpected('family', 'filter', 'entity'))->during('addFieldFilter', ['family', 'IN', 'WRONG']);
    }

    function it_throws_an_exception_if_values_in_array_are_not_integers()
    {
        $this->shouldThrow(InvalidArgumentException::arrayExpected('family', 'filter', 'entity'))->during('addFieldFilter', ['family', 'IN', 'WRONG']);
    }
}
