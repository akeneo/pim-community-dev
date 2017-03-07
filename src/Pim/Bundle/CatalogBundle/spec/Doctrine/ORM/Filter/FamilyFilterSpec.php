<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Prophecy\Argument;

class FamilyFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->beConstructedWith(['family'], ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['family']);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_filter_on_codes_by_default($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.family', Argument::any())->willReturn($qb);
        $qb->andWhere('filterfamily.code IN ("foo", "bar")')->willReturn($qb);

        $expr->in(Argument::any(), ['foo', 'bar'])->willReturn('filterfamily.code IN ("foo", "bar")');
        $qb->expr()->willReturn($expr);

        $this->addFieldFilter('family', 'IN', ['foo', 'bar']);
    }

    function it_adds_a_filter_on_codes($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.family', Argument::any())->willReturn($qb);
        $qb->andWhere('filterfamily.code IN ("foo", "bar")')->willReturn($qb);

        $expr->in(Argument::any(), ['foo', 'bar'])->willReturn('filterfamily.code IN ("foo", "bar")');
        $qb->expr()->willReturn($expr);

        $this->addFieldFilter('family', 'IN', ['foo', 'bar']);
    }

    function it_adds_a_in_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.family', Argument::any())->willReturn($qb);
        $qb->andWhere('filterfamily.code IN ("foo", "bar")')->willReturn($qb);

        $expr->in(Argument::any(), ['foo', 'bar'])->willReturn('filterfamily.code IN ("foo", "bar")');
        $qb->expr()->willReturn($expr);

        $this->addFieldFilter('family', 'IN', ['foo', 'bar']);
    }

    function it_adds_an_empty_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.family', Argument::any())->willReturn($qb);
        $qb->andWhere('filterfamily.code IS NULL')->willReturn($qb);

        $expr->isNull(Argument::any())->willReturn('filterfamily.code IS NULL');
        $qb->expr()->willReturn($expr);

        $this->addFieldFilter('family', 'EMPTY', null);
    }

    function it_adds_a_not_in_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.family', Argument::any())->willReturn($qb);
        $qb->andWhere('filterfamily.code NOT IN ("foo")'.'filterfamily.code IS NULL')->willReturn($qb);
        $qb->expr()->willReturn($expr);

        $expr->notIn(Argument::any(), ['foo'])->willReturn('filterfamily.code NOT IN');
        $expr->isNull(Argument::any())->willReturn('filterfamily.code IS NULL');

        $expr->orX('filterfamily.code NOT IN', 'filterfamily.code IS NULL')
            ->shouldBeCalled()
            ->willReturn('filterfamily.code NOT IN ("foo")'.'filterfamily.code IS NULL');

        $this->addFieldFilter('family', 'NOT IN', ['foo']);
    }

    function it_adds_a_not_empty_filter_on_a_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAlias()->willReturn('f');
        $qb->leftJoin('f.family', Argument::any())->willReturn($qb);
        $qb->expr()->willReturn($expr);
        $qb->andWhere('filterfamily.id IS NOT NULL')->willReturn($qb);

        $expr->isNotNull(Argument::any())->shouldBeCalled()->willReturn('filterfamily.id IS NOT NULL');

        $this->addFieldFilter('family', 'NOT EMPTY', []);
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('family')->shouldReturn(true);
        $this->supportsField('other')->shouldReturn(false);
    }

    function it_throws_an_exception_if_value_is_not_an_array()
    {
        $this->shouldThrow(InvalidPropertyTypeException::arrayExpected(
            'family',
            'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\FamilyFilter',
            'WRONG'
        ))->during('addFieldFilter', ['family', 'IN', 'WRONG']);
    }

    function it_throws_an_exception_if_values_in_array_are_not_strings_or_numerics()
    {
        $this->shouldThrow(InvalidPropertyTypeException::stringExpected(
            'family',
            'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\FamilyFilter',
            false
        ))->during('addFieldFilter', ['family', 'IN', [false]]);
    }
}
