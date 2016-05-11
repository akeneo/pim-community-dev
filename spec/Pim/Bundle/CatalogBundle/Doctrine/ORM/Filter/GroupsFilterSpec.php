<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Prophecy\Argument;

class GroupsFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, ObjectIdResolverInterface $objectIdResolver)
    {
        $this->beConstructedWith($objectIdResolver, ['groups'], ['IN', 'NOT IN']);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'NOT IN']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_in_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.groups', Argument::any())->willReturn($qb);
        $qb->andWhere('filtergroups.id IN (1, 2)')->willReturn($qb);

        $expr->in(Argument::any(), [1, 2])->willReturn('filtergroups.id IN (1, 2)');
        $qb->expr()->willReturn($expr);

        $this->addFieldFilter('groups.id', 'IN', [1, 2]);
    }

    function it_adds_an_empty_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.groups', Argument::any())->willReturn($qb);
        $qb->andWhere('filtergroups.id IS NULL')->willReturn($qb);

        $expr->isNull(Argument::any())->willReturn('filtergroups.id IS NULL');
        $qb->expr()->willReturn($expr);

        $this->addFieldFilter('groups.id', 'EMPTY', null);
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
        $notInQb->select(Argument::containingString('.id'))->shouldBeCalled()->willReturn($notInQb);
        $notInQb->from(
            'ProductClassName',
            Argument::any(),
            Argument::containingString('.id')
        )->shouldBeCalled()->willReturn($notInQb);
        $notInQb->getRootAlias()->willReturn('ep');
        $notInQb->innerJoin(
            Argument::containingString('ep.groups'),
            Argument::containingString('filtergroups')
        )->shouldBeCalled()->willReturn($notInQb);
        $notInQb->expr()->willReturn($expr);
        $expr->in(Argument::containingString('.id'), [3])
            ->shouldBeCalled()
            ->willReturn($inFunc);
        $notInQb->where($inFunc)->shouldBeCalled();
        $notInQb->getDQL()->willReturn('excluded products DQL');

        $qb->expr()->willReturn($expr);
        $expr->notIn('f.id', 'excluded products DQL')
            ->shouldBeCalled()
            ->willReturn($whereFunc);
        $qb->andWhere($whereFunc)->shouldBeCalled();

        $this->addFieldFilter('groups.id', 'NOT IN', [3]);
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('groups')->shouldReturn(true);
        $this->supportsField('other')->shouldReturn(false);
    }

    function it_throws_an_exception_if_value_is_not_an_array()
    {
        $this->shouldThrow(InvalidArgumentException::arrayExpected('groups', 'filter', 'groups', gettype('WRONG')))->during('addFieldFilter', ['groups', 'IN', 'WRONG']);
    }

    function it_throws_an_exception_if_values_in_array_are_not_integers()
    {
        $this->shouldThrow(InvalidArgumentException::arrayExpected('groups', 'filter', 'groups', gettype('WRONG')))->during('addFieldFilter', ['groups', 'IN', 'WRONG']);
    }
}
