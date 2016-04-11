<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\Query\Builder
 */
class CompletenessFilterSpec extends ObjectBehavior
{
    function let(Builder $queryBuilder)
    {
        $this->beConstructedWith(['completeness'], ['=', '<', '>', '>=', '<=', '!=']);
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['=', '<', '>', '>=', '<=', '!=']);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_an_equals_filter_on_completeness_in_the_query($queryBuilder)
    {
        $queryBuilder->field('normalizedData.completenesses.mobile-en_US')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->equals(100)->shouldBeCalled();

        $this->addFieldFilter('completeness', '=', 100, 'en_US', 'mobile');
    }

    function it_adds_a_not_equals_filter_on_completeness_in_the_query($queryBuilder)
    {
        $queryBuilder->field('normalizedData.completenesses.mobile-en_US')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->exists(true)->shouldBeCalled();
        $queryBuilder->notEqual(100)->shouldBeCalled();

        $this->addFieldFilter('completeness', '!=', 100, 'en_US', 'mobile');
    }

    function it_adds_a_lower_than_filter_on_completeness_in_the_query($queryBuilder)
    {
        $queryBuilder->field('normalizedData.completenesses.mobile-en_US')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->lt(100)->shouldBeCalled();

        $this->addFieldFilter('completeness', '<', 100, 'en_US', 'mobile');
    }

    function it_adds_a_greater_than_filter_on_completeness_in_the_query($queryBuilder)
    {
        $queryBuilder->field('normalizedData.completenesses.mobile-en_US')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->gt(55)->shouldBeCalled();

        $this->addFieldFilter('completeness', '>', 55, 'en_US', 'mobile');
    }

    function it_adds_a_greater_or_equal_than_filter_on_completeness_in_the_query($queryBuilder)
    {
        $queryBuilder->field('normalizedData.completenesses.mobile-en_US')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->gte(55)->shouldBeCalled();

        $this->addFieldFilter('completeness', '>=', 55, 'en_US', 'mobile');
    }

    function it_adds_a_lower_or_equal_than_filter_on_completeness_in_the_query($queryBuilder)
    {
        $queryBuilder->field('normalizedData.completenesses.mobile-en_US')->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->lte(60)->shouldBeCalled();

        $this->addFieldFilter('completeness', '<=', 60, 'en_US', 'mobile');
    }

    function it_throws_an_exception_when_the_locale_and_scope_are_not_provided()
    {
        $this
            ->shouldThrow('Pim\Component\Catalog\Exception\InvalidArgumentException')
            ->duringAddFieldFilter('completeness', '=', 100);
        $this
            ->shouldThrow('Pim\Component\Catalog\Exception\InvalidArgumentException')
            ->duringAddFieldFilter('completeness', '=', 100, null, 'ecommerce');
        $this
            ->shouldThrow('Pim\Component\Catalog\Exception\InvalidArgumentException')
            ->duringAddFieldFilter('completeness', '=', 100, 'fr_FR', null);
    }

    function it_throws_an_exception_if_value_is_not_an_integer()
    {
        $this->shouldThrow(
            InvalidArgumentException::numericExpected('completeness', 'filter', 'completeness', gettype('123'))
        )->during('addFieldFilter', ['completeness', '=', '12a3', 'fr_FR', 'mobile']);
    }
}
