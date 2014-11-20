<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class CompletenessFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->beConstructedWith(['completeness'], ['=', '<']);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=', '<']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_equals_filter_on_completeness_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->field('normalizedData.completenesses.mobile-en_US')->willReturn($queryBuilder);
        $queryBuilder->equals('100')->willReturn($queryBuilder);

        $this->addFieldFilter('completenesses', '=', '100', 'en_US', 'mobile');
    }

    function it_adds_a_less_than_filter_on_completeness_in_the_query(Builder $queryBuilder)
    {
        $queryBuilder->field('normalizedData.completenesses.mobile-en_US')->willReturn($queryBuilder);
        $queryBuilder->lt('100')->willReturn($queryBuilder);

        $this->addFieldFilter('completenesses', '<', '100', 'en_US', 'mobile');
    }

    function it_throws_an_exception_when_the_locale_and_scope_are_not_provided()
    {
        $this
            ->shouldThrow('\InvalidArgumentException')
            ->duringAddFieldFilter('completenesses', '=', 100);
        $this
            ->shouldThrow('\InvalidArgumentException')
            ->duringAddFieldFilter('completenesses', '=', 100, null, 'ecommerce');
        $this
            ->shouldThrow('\InvalidArgumentException')
            ->duringAddFieldFilter('completenesses', '=', 100, 'fr_FR', null);
    }

    function it_throws_an_exception_if_value_is_not_a_string()
    {
        $this->shouldThrow(InvalidArgumentException::stringExpected('completeness', 'filter', 'completeness'))
            ->during('addFieldFilter', ['completeness', '=', 123]);
    }

    function it_throws_an_exception_if_value_is_not_a_string_or_an_array()
    {
        $this->shouldThrow(
            InvalidArgumentException::expected('release_date', 'array or string', 'filter', 'date')
        )->during('addFieldFilter', ['release_date', '>', 123]);
    }

    function it_throws_an_error_if_data_is_not_a_valid_date_format()
    {
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
