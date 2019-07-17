<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\IdFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Prophecy\Argument;

class IdFilterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            ['id'],
            ['IN', 'NOT IN', '=', '!='],
            'product_'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IdFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            [
                'IN',
                'NOT IN',
                '=',
                '!=',
            ]
        );
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(false);
    }

    function it_supports_family_field()
    {
        $this->supportsField('id')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_in_list(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'terms' => [
                    'id' => ['product_4F3FCFEC-2448-11E7-93AE-92361F002671', 'product_5F61FD3C-2448-11E7-93AE-92361F002671'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'id',
            Operators::IN_LIST,
            ['4F3FCFEC-2448-11E7-93AE-92361F002671', '5F61FD3C-2448-11E7-93AE-92361F002671'],
            null,
            null,
            []
        );
    }

    function it_adds_a_filter_with_operator_not_in_list(SearchQueryBuilder $sqb)
    {
        $sqb->addMustNot(
            [
                'terms' => [
                    'id' => ['product_4F3FCFEC-2448-11E7-93AE-92361F002671', 'product_5F61FD3C-2448-11E7-93AE-92361F002671'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'id',
            Operators::NOT_IN_LIST,
            ['4F3FCFEC-2448-11E7-93AE-92361F002671', '5F61FD3C-2448-11E7-93AE-92361F002671'],
            null,
            null,
            []
        );
    }

    function it_adds_a_filter_with_operator_equal(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'term' => [
                    'id' => 'product_4F3FCFEC-2448-11E7-93AE-92361F002671',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('id', Operators::EQUALS, '4F3FCFEC-2448-11E7-93AE-92361F002671', null, null, []);
    }

    function it_adds_a_filter_with_operator_not_equal(SearchQueryBuilder $sqb)
    {
        $sqb->addMustNot(
            [
                'term' => [
                    'id' => 'product_4F3FCFEC-2448-11E7-93AE-92361F002671',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'id',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('id', Operators::NOT_EQUAL, '4F3FCFEC-2448-11E7-93AE-92361F002671', null, null, []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during(
            'addFieldFilter',
            [
                'id',
                Operators::IN_LIST,
                ['4F3FCFEC-2448-11E7-93AE-92361F002671', '5F61FD3C-2448-11E7-93AE-92361F002671'],
                null,
                null,
                []
            ]
        );
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_in_list(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'id',
                IdFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['id', Operators::IN_LIST, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_not_in_list(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'id',
                IdFilter::class,
                'NOT_AN_ARRAY'
            )
        )->during('addFieldFilter', ['id', Operators::NOT_IN_LIST, 'NOT_AN_ARRAY', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string_with_equals(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'id',
                IdFilter::class,
                [false]
            )
        )->during('addFieldFilter', ['id', Operators::EQUALS, [false], null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string_with_not_equals(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'id',
                IdFilter::class,
                [false]
            )
        )->during('addFieldFilter', ['id', Operators::NOT_EQUAL, [false], null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                IdFilter::class
            )
        )->during('addFieldFilter', ['id', Operators::IN_CHILDREN_LIST, ['5f61fd3c-2448-11e7-93ae-92361f002671'], null, null, []]);
    }
}
