<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class DateFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->beConstructedWith(['pim_catalog_date'], ['created', 'updated'], ['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY']);
        $this->setQueryBuilder($queryBuilder);

        $queryBuilder->field(Argument::any())->willReturn($queryBuilder);
    }

    function it_is_an_attribute_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface');
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY']);

        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_less_than_filter_on_an_attribute_value_in_the_query($queryBuilder, AttributeInterface $date)
    {
        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(false);
        $date->isScopable()->willReturn(false);
        $queryBuilder->lt(strtotime('2014-03-15'))->willReturn($queryBuilder);

        $this->addAttributeFilter($date, '<', '2014-03-15');
    }

    function it_adds_a_greater_than_filter_on_an_attribute_value_in_the_query($queryBuilder, AttributeInterface $date)
    {
        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(false);
        $date->isScopable()->willReturn(false);
        $queryBuilder->gt(strtotime('2014-03-15 23:59:59'))->willReturn($queryBuilder);

        $this->addAttributeFilter($date, '>', '2014-03-15');
    }

    function it_adds_a_between_filter_on_an_attribute_value_in_the_query($queryBuilder, AttributeInterface $date)
    {
        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(false);
        $date->isScopable()->willReturn(false);
        $queryBuilder->gte(strtotime('2014-03-15'))->willReturn($queryBuilder);
        $queryBuilder->lte(strtotime('2014-03-20 23:59:59'))->willReturn($queryBuilder);

        $this->addAttributeFilter($date, 'BETWEEN', ['2014-03-15', '2014-03-20']);
    }

    function it_adds_a_not_between_filter_on_an_attribute_value_in_the_query($queryBuilder, AttributeInterface $date)
    {
        $date->getCode()->willReturn('release_date');
        $date->isLocalizable()->willReturn(false);
        $date->isScopable()->willReturn(false);
        $queryBuilder->expr()->willReturn($queryBuilder);
        $queryBuilder->addAnd($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->addOr($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->addOr($queryBuilder)->willReturn($queryBuilder);
        $queryBuilder->lte(strtotime('2014-03-15'))->willReturn($queryBuilder);
        $queryBuilder->gte(strtotime('2014-03-20 23:59:59'))->willReturn($queryBuilder);

        $this->addAttributeFilter($date, ['from' => '<', 'to' => '>'], ['2014-03-15', '2014-03-20']);
    }

    function it_adds_a_between_filter_on_a_field_in_the_query($queryBuilder)
    {
        $queryBuilder->gte(strtotime('2014-03-15'))->willReturn($queryBuilder);
        $queryBuilder->lte(strtotime('2014-03-20 23:59:59'))->willReturn($queryBuilder);

        $this->addFieldFilter('created', 'BETWEEN', ['2014-03-15', '2014-03-20']);
    }

    function it_throws_an_exception_if_value_is_not_a_string_or_an_array()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected('release_date', 'array or string', 'filter', 'date')
        )->during('addFieldFilter', ['release_date', '>', 123]);
    }

    function it_throws_an_error_if_data_is_not_a_valid_date_format() {
        $this->shouldThrow(
            InvalidArgumentException::expected('release_date', 'a string with the format yyyy-mm-dd', 'filter', 'date')
        )->during('addFieldFilter', ['release_date', '>', ['not a valid date format', 'WRONG']]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_strings()
    {
        $this->shouldThrow(
            InvalidArgumentException::stringExpected('release_date', 'filter', 'date')
        )->during('addFieldFilter', ['release_date', '>', [123, 123]]);
    }

    function it_throws_an_exception_if_value_is_an_array_but_does_not_contain_two_values()
    {
        $this->shouldThrow(
            InvalidArgumentException::stringExpected('release_date', 'filter', 'date')
        )->during('addFieldFilter', ['release_date', '>', [123, 123, 'three']]);
    }
}
