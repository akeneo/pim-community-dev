<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class DateTimeFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->beConstructedWith(
            ['created', 'updated'],
            ['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY', 'NOT EMPTY', '!=']
        );
        $this->setQueryBuilder($queryBuilder);

        $queryBuilder->field(Argument::any())->willReturn($queryBuilder);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY', 'NOT EMPTY', '!=']);

        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_an_equal_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->equals(strtotime('2014-03-15 12:03:00'))->shouldBeCalledTimes(2);

        $this->addFieldFilter('updated', '=', '2014-03-15 12:03:00');
        $this->addFieldFilter('updated', '=', new \DateTime('2014-03-15 12:03:00'));
    }

    function it_adds_a_less_than_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->lt(strtotime('2014-03-15 12:03:00'))->shouldBeCalledTimes(2);

        $this->addFieldFilter('updated', '<', '2014-03-15 12:03:00');
        $this->addFieldFilter('updated', '<', new \DateTime('2014-03-15 12:03:00'));
    }

    function it_adds_a_greater_than_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->gt(strtotime('2014-03-15 12:03:00'))->shouldBeCalledTimes(2);

        $this->addFieldFilter('updated', '>', '2014-03-15 12:03:00');
        $this->addFieldFilter('updated', '>', new \DateTime('2014-03-15 12:03:00'));
    }

    function it_adds_an_empty_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->exists(false)->shouldBeCalled();

        $this->addFieldFilter('updated', 'EMPTY', null);
    }

    function it_adds_a_not_empty_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->exists(true)->shouldBeCalled();

        $this->addFieldFilter('updated', 'NOT EMPTY', null);
    }

    function it_adds_a_between_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->gte(strtotime('2014-03-15 12:03:00'))->shouldBeCalledTimes(2);
        $queryBuilder->lte(strtotime('2014-03-20 12:03:00'))->shouldBeCalledTimes(2);

        $this->addFieldFilter('updated', 'BETWEEN', ['2014-03-15 12:03:00', '2014-03-20 12:03:00']);
        $this->addFieldFilter('updated', 'BETWEEN', [new \DateTime('2014-03-15 12:03:00'), new \DateTime('2014-03-20 12:03:00')]);
    }

    function it_adds_a_not_between_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->expr()->willReturn($queryBuilder);
        $queryBuilder->addAnd($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->addOr($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->lt(strtotime('2014-03-15 12:03:00'))->willReturn($queryBuilder)->shouldBeCalledTimes(2);
        $queryBuilder->gt(strtotime('2014-03-20 12:03:00'))->willReturn($queryBuilder)->shouldBeCalledTimes(2);

        $this->addFieldFilter('updated', 'NOT BETWEEN', ['2014-03-15 12:03:00', '2014-03-20 12:03:00']);
        $this->addFieldFilter('updated', 'NOT BETWEEN', [new \DateTime('2014-03-15 12:03:00'), new \DateTime('2014-03-20 12:03:00')]);
    }

    function it_throws_an_exception_if_value_is_not_a_string_an_array_or_datetime()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                print_r(123, true)
            )
        )->during('addFieldFilter', ['updated', '>', 123]);
    }

    function it_throws_an_error_if_data_is_not_a_valid_date_format()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated',
                'a string with the format yyyy-mm-dd H:i:s',
                'filter',
                'date',
                'not a valid date format'
            )
        )->during('addFieldFilter', ['updated', '>', ['not a valid date format', 'WRONG']]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_strings_or_dates()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                123
            )
        )->during('addFieldFilter', ['updated', '>', [123, 123]]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_two_values()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected(
                'updated',
                'array with 2 elements, string or \DateTime',
                'filter',
                'date',
                print_r([123, 123, 'three'], true)
            )
        )->during('addFieldFilter', ['updated', '>', [123, 123, 'three']]);
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['created', 'updated']);
    }
}
