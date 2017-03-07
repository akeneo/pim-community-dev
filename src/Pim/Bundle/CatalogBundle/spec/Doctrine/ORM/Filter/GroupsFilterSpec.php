<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Prophecy\Argument;

class GroupsFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->beConstructedWith(['groups'], ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['groups']);
    }

    function it_adds_a_filter_on_codes_by_default($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.groups', Argument::any())->willReturn($qb);
        $qb->andWhere('filtergroups.code IN ("foo", "bar")')->willReturn($qb);

        $expr->in(Argument::any(), ['foo', 'bar'])->willReturn('filtergroups.code IN ("foo", "bar")');
        $qb->expr()->willReturn($expr);

        $this->addFieldFilter('groups', 'IN', ['foo', 'bar']);
    }

    function it_adds_a_filter_on_codes($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.groups', Argument::any())->willReturn($qb);
        $qb->andWhere('filtergroups.code IN ("foo", "bar")')->willReturn($qb);

        $expr->in(Argument::any(), ['foo', 'bar'])->willReturn('filtergroups.code IN ("foo", "bar")');
        $qb->expr()->willReturn($expr);

        $this->addFieldFilter('groups', 'IN', ['foo', 'bar']);
    }

    function it_adds_a_in_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.groups', Argument::any())->willReturn($qb);
        $qb->andWhere('filtergroups.code IN ("foo", "bar")')->willReturn($qb);

        $expr->in(Argument::any(), ['foo', 'bar'])->willReturn('filtergroups.code IN ("foo", "bar")');
        $qb->expr()->willReturn($expr);

        $this->addFieldFilter('groups', 'IN', ['foo', 'bar']);
    }

    function it_adds_an_empty_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.groups', Argument::any())->willReturn($qb);
        $qb->andWhere('filtergroups.code IS NULL')->willReturn($qb);

        $expr->isNull(Argument::any())->willReturn('filtergroups.code IS NULL');
        $qb->expr()->willReturn($expr);

        $this->addFieldFilter('groups', 'EMPTY', null);
    }

    function it_adds_an_not_in_filter_on_a_field_in_the_query(
        $qb,
        EntityManager $em,
        QueryBuilder $notInQb,
        Expr $expr,
        Expr\Func $inFunc,
        Expr\Func $whereFunc
    ) {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.groups', Argument::containingString('filtergroups'))->willReturn($qb);
        $qb->getEntityManager()->willReturn($em);
        $em->createQueryBuilder()->willReturn($notInQb);
        $qb->getRootEntities()->willReturn(['ProductClassName']);
        $notInQb->select(Argument::containingString('.code'))->shouldBeCalled()->willReturn($notInQb);
        $notInQb->from(
            'ProductClassName',
            Argument::any(),
            Argument::containingString('.code')
        )->shouldBeCalled()->willReturn($notInQb);
        $notInQb->getRootAlias()->willReturn('ep');
        $notInQb->innerJoin(
            Argument::containingString('ep.groups'),
            Argument::containingString('filtergroups')
        )->shouldBeCalled()->willReturn($notInQb);
        $notInQb->expr()->willReturn($expr);
        $expr->in(Argument::containingString('.code'), ["foo"])
            ->shouldBeCalled()
            ->willReturn($inFunc);
        $notInQb->where($inFunc)->shouldBeCalled();
        $notInQb->getDQL()->willReturn('excluded products DQL');

        $qb->expr()->willReturn($expr);
        $expr->notIn('f.code', 'excluded products DQL')
            ->shouldBeCalled()
            ->willReturn($whereFunc);
        $qb->andWhere($whereFunc)->shouldBeCalled();

        $this->addFieldFilter('groups', 'NOT IN', ['foo']);
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('groups')->shouldReturn(true);
        $this->supportsField('other')->shouldReturn(false);
    }

    function it_throws_an_exception_if_value_is_not_an_array()
    {
        $this->shouldThrow(InvalidPropertyTypeException::arrayExpected(
            'groups',
            'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\GroupsFilter',
            'WRONG'
        ))->during('addFieldFilter', ['groups', 'IN', 'WRONG']);
    }

    function it_throws_an_exception_if_values_in_array_are_not_strings_or_numerics()
    {
        $this->shouldThrow(InvalidPropertyTypeException::stringExpected(
            'groups',
            'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\GroupsFilter',
            false
        ))->during('addFieldFilter', ['groups', 'IN', [false]]);
    }
}
