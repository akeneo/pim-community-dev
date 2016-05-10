<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Prophecy\Argument;

class DateTimeFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb)
    {
        $this->beConstructedWith(
            ['created', 'updated'],
            ['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY', 'NOT EMPTY', '!=']
        );
        $this->setQueryBuilder($qb);

        $qb->getRootAliases()->willReturn(['p']);
    }

    function it_is_a_datetime_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateTimeFilter');
    }

    function it_is_a_field_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY', 'NOT EMPTY', '!=']);

        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_date_fields()
    {
        $this->supportsField('created')->shouldReturn(true);
        $this->supportsField('updated')->shouldReturn(true);
        $this->supportsField('other')->shouldReturn(false);
    }

    function it_adds_an_equal_filter_on_an_field_in_the_query(
        $qb,
        Expr $expr,
        Expr\Comparison $comp
    ) {
        $qb->getRootAliases()->willReturn(['p']);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15 12:03:00')->willReturn('2014-03-15 12:03:00');
        $expr->eq('p.updated_at', '2014-03-15 12:03:00')->willReturn($comp);
        $comp->__toString()->willReturn('p.updated_at = \'2014-03-15 12:03:00\'');

        $qb->andWhere('p.updated_at = \'2014-03-15 12:03:00\'')->shouldBeCalled();

        $this->addFieldFilter('updated_at', '=', '2014-03-15 12:03:00');
    }

    function it_adds_a_not_equal_filter_on_an_field_in_the_query(
        $qb,
        Expr $expr,
        Expr\Comparison $comp
    ) {
        $qb->getRootAliases()->willReturn(['p']);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15 12:03:00')->willReturn('2014-03-15 12:03:00');
        $expr->neq('p.updated_at', '2014-03-15 12:03:00')->willReturn($comp);
        $comp->__toString()->willReturn('p.updated_at != \'2014-03-15 12:03:00\'');

        $qb->andWhere('p.updated_at != \'2014-03-15 12:03:00\'')->shouldBeCalled();

        $this->addFieldFilter('updated_at', '!=', '2014-03-15 12:03:00');
    }

    function it_adds_a_less_than_filter_on_an_field_in_the_query(
        $qb,
        Expr $expr,
        Expr\Comparison $comp
    ) {
        $qb->getRootAliases()->willReturn(['p']);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15 12:03:00')->willReturn('2014-03-15 12:03:00');
        $expr->lt('p.updated_at', '2014-03-15 12:03:00')->willReturn($comp);
        $comp->__toString()->willReturn('p.updated_at < \'2014-03-15 12:03:00\'');

        $qb->andWhere('p.updated_at < \'2014-03-15 12:03:00\'')->shouldBeCalled();

        $this->addFieldFilter('updated_at', '<', '2014-03-15 12:03:00');
    }

    function it_adds_a_greater_than_filter_on_an_field_in_the_query(
        $qb,
        Expr $expr,
        Expr\Comparison $comp
    ) {
        $qb->getRootAliases()->willReturn(['p']);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15 12:03:00')->willReturn('2014-03-15 12:03:00');
        $expr->gt('p.updated_at', '2014-03-15 12:03:00')->willReturn($comp);
        $comp->__toString()->willReturn('p.updated_at > \'2014-03-15 12:03:00\'');

        $qb->andWhere('p.updated_at > \'2014-03-15 12:03:00\'')->shouldBeCalled();

        $this->addFieldFilter('updated_at', '>', '2014-03-15 12:03:00');
    }

    function it_adds_an_empty_filter_on_an_field_in_the_query($qb, Expr $expr)
    {
        $qb->expr()->willReturn($expr);
        $expr->isNull('p.updated_at')->willReturn('p.updated_at IS NULL');

        $qb->andWhere('p.updated_at IS NULL')->shouldBeCalled();

        $this->addFieldFilter('updated_at', 'EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_an_field_in_the_query($qb, Expr $expr)
    {
        $qb->expr()->willReturn($expr);
        $expr->isNotNull('p.updated_at')->shouldBeCalled()->willReturn('p.updated_at IS NOT NULL');

        $qb->andWhere('p.updated_at IS NOT NULL')->shouldBeCalled();

        $this->addFieldFilter('updated_at', 'NOT EMPTY', null);
    }

    function it_adds_a_between_filter_on_an_field_in_the_query($qb, Expr $expr)
    {
        $qb->getRootAliases()->willReturn(['p']);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15 12:03:00')->willReturn('2014-03-15 12:03:00');
        $expr->literal('2014-03-16 12:03:00')->willReturn('2014-03-16 12:03:00');

        $qb->andWhere('p.updated_at BETWEEN 2014-03-15 12:03:00 AND 2014-03-16 12:03:00')->shouldBeCalled();

        $this->addFieldFilter('updated_at', 'BETWEEN', ['2014-03-15 12:03:00', '2014-03-16 12:03:00']);
    }

    function it_adds_a_not_between_filter_on_an_field_in_the_query(
        $qb,
        Expr $expr,
        Expr\Comparison $ltComp,
        Expr\Comparison $gtComp,
        Expr\Orx $or
    ) {
        $qb->getRootAliases()->willReturn(['p']);
        $qb->expr()->willReturn($expr);
        $expr->literal('2014-03-15 12:03:00')->willReturn('2014-03-15 12:03:00');
        $expr->literal('2014-03-16 12:03:00')->willReturn('2014-03-16 12:03:00');
        $expr->lt('p.updated_at', '2014-03-15 12:03:00')->willReturn($ltComp);
        $expr->gt('p.updated_at', '2014-03-16 12:03:00')->willReturn($gtComp);
        $expr->orX($ltComp, $gtComp)->willReturn($or);

        $qb->andWhere($or)->shouldBeCalled();

        $this->addFieldFilter('updated_at', 'NOT BETWEEN', ['2014-03-15 12:03:00', '2014-03-16 12:03:00']);
    }

    function it_throws_an_exception_if_value_is_not_a_string_an_array_or_a_datetime()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected('updated_at', 'array with 2 elements, string or \DateTime', 'filter', 'date', print_r(123, true))
        )->during('addFieldFilter', ['updated_at', '>', 123]);
    }

    function it_throws_an_error_if_data_is_not_a_valid_date_format()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected('updated_at', 'a string with the format Y-m-d H:i:s', 'filter', 'date', 'not a valid date format')
        )->during('addFieldFilter', ['updated_at', '>', ['not a valid date format', 'WRONG']]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_strings_or_dates()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated_at',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                123
            )
        )->during('addFieldFilter', ['updated_at', '>', [123, 123]]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_two_values()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated_at',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                print_r([123, 123, 'three'], true)
            )
        )->during('addFieldFilter', ['updated_at', '>', [123, 123, 'three']]);
    }
}
