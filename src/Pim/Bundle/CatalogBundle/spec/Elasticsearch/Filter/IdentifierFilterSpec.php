<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\AbstractFieldFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\IdentifierFilter;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

class IdentifierFilterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            ['identifier'],
            ['pim_catalog_identifier'],
            [
                'STARTS WITH',
                'CONTAINS',
                'DOES NOT CONTAIN',
                '=',
                '!=',
                'IN LIST',
                'NOT IN LIST'
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IdentifierFilter::class);
    }

    function it_is_a_field_filter_and_an_attribute_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            [
                'STARTS WITH',
                'CONTAINS',
                'DOES NOT CONTAIN',
                '=',
                '!=',
                'IN LIST',
                'NOT IN LIST'
            ]

        );
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(true);
        $this->supportsOperator('EMPTY')->shouldReturn(false);
    }

    function it_supports_identifier_field()
    {
        $this->supportsField('identifier')->shouldReturn(true);
        $this->supportsField('sku')->shouldReturn(false);
    }

    function it_supports_identifier_attribute(AttributeInterface $sku, AttributeInterface $price)
    {
        $sku->getType()->willReturn('pim_catalog_identifier');
        $price->getType()->willReturn('pim_catalog_price');

        $this->supportsAttribute($sku)->shouldReturn(true);
        $this->supportsAttribute($price)->shouldReturn(false);
    }

    function it_adds_a_field_filter_with_operator_starts_with(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'identifier',
                    'query'         => 'sku-*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::STARTS_WITH, 'sku-', null, null, []);
    }

    function it_adds_a_field_filter_with_operator_contains(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'identifier',
                    'query'         => '*001*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::CONTAINS, '001', null, null, []);
    }

    function it_adds_a_field_filter_with_operator_not_contains(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'identifier',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addMustNot(
            [
                'query_string' => [
                    'default_field' => 'identifier',
                    'query'         => '*001*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::DOES_NOT_CONTAIN, '001', null, null, []);
    }

    function it_adds_a_field_filter_with_operator_equals(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'term' => [
                    'identifier' => 'sku-001',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::EQUALS, 'sku-001', null, null, []);
    }

    function it_adds_a_field_filter_with_operator_not_equal(SearchQueryBuilder $sqb)
    {
        $sqb->addMustNot(
            [
                'term' => [
                    'identifier' => 'sku-001',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'identifier',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::NOT_EQUAL, 'sku-001', null, null, []);
    }

    function it_adds_a_field_filter_with_operator_in_list(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'terms' => [
                    'identifier' => ['sku-001'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::IN_LIST, ['sku-001'], null, null, []);
    }

    function it_adds_a_field_filter_with_operator_not_in_list(SearchQueryBuilder $sqb)
    {
        $sqb->addMustNot(
            [
                'terms' => [
                    'identifier' => ['sku-001'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::NOT_IN_LIST, ['sku-001'], null, null, []);
    }

    function it_adds_an_attribute_filter_with_operator_starts_with(SearchQueryBuilder $sqb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'identifier',
                    'query'         => 'sku-*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($sku, Operators::STARTS_WITH, 'sku-', null, null, []);
    }

    function it_adds_an_attribute_filter_with_operator_contains(SearchQueryBuilder $sqb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'identifier',
                    'query'         => '*001*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($sku, Operators::CONTAINS, '001', null, null, []);
    }

    function it_adds_an_attribute_filter_with_operator_not_contains(SearchQueryBuilder $sqb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'identifier',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addMustNot(
            [
                'query_string' => [
                    'default_field' => 'identifier',
                    'query'         => '*001*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($sku, Operators::DOES_NOT_CONTAIN, '001', null, null, []);
    }

    function it_adds_an_attribute_filter_with_operator_equals(SearchQueryBuilder $sqb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sqb->addFilter(
            [
                'term' => [
                    'identifier' => 'sku-001',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($sku, Operators::EQUALS, 'sku-001', null, null, []);
    }

    function it_adds_an_attribute_filter_with_operator_not_equal(SearchQueryBuilder $sqb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sqb->addMustNot(
            [
                'term' => [
                    'identifier' => 'sku-001',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'identifier',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($sku, Operators::NOT_EQUAL, 'sku-001', null, null, []);
    }

    function it_adds_an_attribute_filter_with_operator_in_list(SearchQueryBuilder $sqb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sqb->addFilter(
            [
                'terms' => [
                    'identifier' => ['sku-001'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($sku, Operators::IN_LIST, ['sku-001'], null, null, []);
    }

    function it_adds_an_attribute_filter_with_operator_not_in_list(SearchQueryBuilder $sqb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sqb->addMustNot(
            [
                'terms' => [
                    'identifier' => ['sku-001'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($sku, Operators::NOT_IN_LIST, ['sku-001'], null, null, []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $sku)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['identifier', Operators::EQUALS, 'sku-001', null,  null, []]);

        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$sku, Operators::EQUALS, 'sku-001', null,  null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string_with_unsupported_operator_for_field_filter(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'identifier',
                IdentifierFilter::class,
                ['sku-001']
            )
        )->during('addFieldFilter', ['identifier', Operators::EQUALS, ['sku-001'], null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_unsupported_operator_for_field_filter(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'identifier',
                IdentifierFilter::class,
                'sku-001'
            )
        )->during('addFieldFilter', ['identifier', Operators::IN_LIST, 'sku-001', null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator_for_field_filter(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                IdentifierFilter::class
            )
        )->during('addFieldFilter', ['identifier', Operators::IN_CHILDREN_LIST, 'sku-001', null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string_with_unsupported_operator_for_attribute_filter(
        SearchQueryBuilder $sqb,
        AttributeInterface $sku
    ) {
        $sku->getCode()->willReturn('sku');
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'sku',
                IdentifierFilter::class,
                ['sku-001']
            )
        )->during('addAttributeFilter', [$sku, Operators::EQUALS, ['sku-001'], null, null, []]);
    }

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_unsupported_operator_for_attribute_filter(
        SearchQueryBuilder $sqb,
        AttributeInterface $sku
    ) {
        $sku->getCode()->willReturn('sku');
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'sku',
                IdentifierFilter::class,
                'sku-001'
            )
        )->during('addAttributeFilter', [$sku, Operators::IN_LIST, 'sku-001', null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator_for_attribute_filter(
        SearchQueryBuilder $sqb,
        AttributeInterface $sku
    ) {
        $sku->getCode()->willReturn('sku');
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                IdentifierFilter::class
            )
        )->during('addAttributeFilter', [$sku, Operators::IN_CHILDREN_LIST, 'sku-001', null, null, []]);
    }
}
