<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\AbstractAttributeFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute\IdentifierFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

class IdentifierFilterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
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

    function it_is_a_filter()
    {
        $this->shouldImplement(AttributeFilterInterface::class);
        $this->shouldBeAnInstanceOf(AbstractAttributeFilter::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn([
            'STARTS WITH',
            'CONTAINS',
            'DOES NOT CONTAIN',
            '=',
            '!=',
            'IN LIST',
            'NOT IN LIST'
        ]);
        $this->supportsOperator('=')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_supports_identifier_attribute(AttributeInterface $sku, AttributeInterface $price)
    {
        $sku->getType()->willReturn('pim_catalog_identifier');
        $price->getType()->willReturn('pim_catalog_price');

        $this->supportsAttribute($sku)->shouldReturn(true);
        $this->supportsAttribute($price)->shouldReturn(false);
    }


    function it_adds_an_attribute_filter_with_operator_equals(AttributeInterface $sku, SearchQueryBuilder $sqb)
    {
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('text');
        $sqb->addFilter(
            [
                'term' => [
                    'values.sku-text.<all_channels>.<all_locales>' => 'sku-001',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($sku, Operators::EQUALS, 'sku-001', null, null, []);
    }

    function it_adds_an_attribute_filter_with_operator_starts_with(SearchQueryBuilder $sqb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('identifier');
        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'values.sku-identifier.<all_channels>.<all_locales>',
                    'query'         => 'sku\-*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($sku, Operators::STARTS_WITH, 'sku-', null, null, []);
    }

    function it_adds_an_attribute_filter_with_operator_contains(SearchQueryBuilder $sqb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('identifier');
        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'values.sku-identifier.<all_channels>.<all_locales>',
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
        $sku->getBackendType()->willReturn('identifier');
        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.sku-identifier.<all_channels>.<all_locales>',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addMustNot(
            [
                'query_string' => [
                    'default_field' => 'values.sku-identifier.<all_channels>.<all_locales>',
                    'query'         => '*001*',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($sku, Operators::DOES_NOT_CONTAIN, '001', null, null, []);
    }

    function it_adds_an_attribute_filter_with_operator_not_equal(SearchQueryBuilder $sqb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('identifier');
        $sqb->addMustNot(
            [
                'term' => [
                    'values.sku-identifier.<all_channels>.<all_locales>' => 'sku-001',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'values.sku-identifier.<all_channels>.<all_locales>',
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($sku, Operators::NOT_EQUAL, 'sku-001', null, null, []);
    }

    function it_adds_an_attribute_filter_with_operator_in_list(SearchQueryBuilder $sqb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('identifier');
        $sqb->addFilter(
            [
                'terms' => [
                    'values.sku-identifier.<all_channels>.<all_locales>' => ['sku-001'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($sku, Operators::IN_LIST, ['sku-001'], null, null, []);
    }

    function it_adds_an_attribute_filter_with_operator_not_in_list(SearchQueryBuilder $sqb, AttributeInterface $sku)
    {
        $sku->getCode()->willReturn('sku');
        $sku->getBackendType()->willReturn('identifier');
        $sqb->addMustNot(
            [
                'terms' => [
                    'values.sku-identifier.<all_channels>.<all_locales>' => ['sku-001'],
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addAttributeFilter($sku, Operators::NOT_IN_LIST, ['sku-001'], null, null, []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $name)
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addAttributeFilter', [$name, Operators::CONTAINS, 'Sony', 'en_US', 'ecommerce', []]);

    }

    function it_throws_an_exception_when_the_given_value_is_not_a_string_with_unsupported_operator_for_field_filter(
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

    function it_throws_an_exception_when_the_given_value_is_not_an_array_with_unsupported_operator_for_field_filter(
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
}
