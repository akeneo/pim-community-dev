<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Doctrine\Common\ObjectIdResolverInterface;

class GroupsFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, ObjectIdResolverInterface $objectIdResolver)
    {
        $this->beConstructedWith($objectIdResolver, ['groups'], ['IN', 'NOT IN']);
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
        $qb->leftJoin('f.groups', 'filtergroups')->shouldBeCalled()->willReturn($qb);
        $qb->andWhere('filtergroups.id IN (1, 2)')->shouldBeCalled()->willReturn($qb);

        $expr->in('filtergroups.id', [1, 2])->shouldBeCalled()->willReturn('filtergroups.id IN (1, 2)');
        $qb->expr()->shouldBeCalled()->willReturn($expr);

        $this->addFieldFilter('groups', 'IN', [1, 2]);
    }

    function it_adds_an_empty_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->shouldBeCalled()->willReturn('f');
        $qb->leftJoin('f.groups', 'filtergroups')->shouldBeCalled()->willReturn($qb);
        $qb->andWhere('filtergroups.id IS NULL')->shouldBeCalled()->willReturn($qb);

        $expr->isNull('filtergroups.id')->shouldBeCalled()->willReturn('filtergroups.id IS NULL');
        $qb->expr()->shouldBeCalled()->willReturn($expr);

        $this->addFieldFilter('groups', 'EMPTY', null);
    }

    function it_adds_an_not_in_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->shouldBeCalled()->willReturn('f');
        $qb->leftJoin('f.groups', 'filtergroups')->shouldBeCalled()->willReturn($qb);
        $qb->andWhere('filtergroups.id NOT IN(3)' . 'filtergroups.id IS NULL')->shouldBeCalled()->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $expr->notIn('filtergroups'.'.id', [3])->shouldBeCalled()->willReturn('filtergroups.id NOT IN');
        $expr->isNull('filtergroups.id')->shouldBeCalled()->willReturn('filtergroups.id IS NULL');

        $expr->orX('filtergroups.id NOT IN', 'filtergroups.id IS NULL')
            ->shouldBeCalled()
            ->willReturn('filtergroups.id NOT IN(3)' . 'filtergroups.id IS NULL');

        $this->addFieldFilter('groups', 'NOT IN', [3]);
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('groups')->shouldReturn(true);
        $this->supportsField('other')->shouldReturn(false);
    }

    function it_throws_an_exception_if_value_is_not_an_array()
    {
        $this->shouldThrow(InvalidArgumentException::arrayExpected('groups', 'filter', 'groups'))->during('addFieldFilter', ['groups', 'IN', 'WRONG']);
    }

    function it_throws_an_exception_if_values_in_array_are_not_integers()
    {
        $this->shouldThrow(InvalidArgumentException::arrayExpected('groups', 'filter', 'groups'))->during('addFieldFilter', ['groups', 'IN', 'WRONG']);
    }
}
