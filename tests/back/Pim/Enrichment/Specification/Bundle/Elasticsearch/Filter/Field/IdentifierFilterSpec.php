<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\IdentifierFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetMainIdentifierAttributeCode;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use LogicException;
use PhpSpec\ObjectBehavior;

class IdentifierFilterSpec extends ObjectBehavior
{
    function let(
        GetMainIdentifierAttributeCode $getMainIdentifierAttributeCode
    )
    {
        $this->beConstructedWith(
            $getMainIdentifierAttributeCode,
            ['identifier'],
            [
                'STARTS WITH',
                'CONTAINS',
                'DOES NOT CONTAIN',
                '=',
                '!=',
                'IN LIST',
                'NOT IN LIST',
                'EMPTY',
                'NOT EMPTY'
            ]
        );

        $getMainIdentifierAttributeCode->__invoke()->willReturn('sku');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IdentifierFilter::class);
    }

    function it_is_a_field_filter_and_an_attribute_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
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
                'NOT IN LIST',
                'EMPTY',
                'NOT EMPTY'
            ]

        );
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(true);
        $this->supportsOperator('IN CHILDREN')->shouldReturn(false);
    }

    function it_supports_identifier_field()
    {
        $this->supportsField('identifier')->shouldReturn(true);
        $this->supportsField('sku')->shouldReturn(false);
    }

    function it_adds_a_field_filter_with_operator_starts_with(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            ["bool" => ["should" => [["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface"]], ["query_string" => ["default_field" => "values.sku-text.<all_channels>.<all_locales>", "query" => "sku\-*"]]]]], ["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface"]], ["query_string" => ["default_field" => "identifier", "query" => "sku\-*"]]]]]], "minimum_should_match" => 1]]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::STARTS_WITH, 'sku-', null, null, []);
    }

    function it_adds_a_field_filter_with_operator_contains(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            ["bool" => ["should" => [["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface"]], ["query_string" => ["default_field" => "values.sku-text.<all_channels>.<all_locales>", "query" => "*001*"]]]]], ["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface"]], ["query_string" => ["default_field" => "identifier", "query" => "*001*"]]]]]], "minimum_should_match" => 1]]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::CONTAINS, '001', null, null, []);
    }

    function it_adds_a_field_filter_with_operator_not_contains(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            ["bool" => ["should" => [["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface"]], ["exists" => ["field" => "values.sku-text.<all_channels>.<all_locales>"]]]]], ["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface"]], ["exists" => ["field" => "identifier"]]]]]], "minimum_should_match" => 1]]
        )->shouldBeCalled();

        $sqb->addMustNot(
            ["bool" => ["should" => [["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface"]], ["query_string" => ["default_field" => "values.sku-text.<all_channels>.<all_locales>", "query" => "*001*"]]]]], ["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface"]], ["query_string" => ["default_field" => "identifier", "query" => "*001*"]]]]]], "minimum_should_match" => 1]]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::DOES_NOT_CONTAIN, '001', null, null, []);
    }

    function it_adds_a_field_filter_with_operator_equals(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            ["bool" => ["should" => [["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface"]], ["term" => ["values.sku-text.<all_channels>.<all_locales>" => "sku\-001"]]]]], ["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface"]], ["term" => ["identifier" => "sku\-001"]]]]]], "minimum_should_match" => 1]]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::EQUALS, 'sku-001', null, null, []);
    }

    function it_adds_a_field_filter_with_operator_not_equal(SearchQueryBuilder $sqb)
    {
        $sqb->addMustNot(
            ["bool" => ["should" => [["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface"]], ["query_string" => ["default_field" => "values.sku-text.<all_channels>.<all_locales>", "query" => "sku\-001"]]]]], ["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface"]], ["query_string" => ["default_field" => "identifier", "query" => "sku\-001"]]]]]], "minimum_should_match" => 1]]
        )->shouldBeCalled();

        $sqb->addFilter(
            ["bool" => ["should" => [["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface"]], ["exists" => ["field" => "values.sku-text.<all_channels>.<all_locales>"]]]]], ["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface"]], ["exists" => ["field" => "identifier"]]]]]], "minimum_should_match" => 1]]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::NOT_EQUAL, 'sku-001', null, null, []);
    }

    function it_adds_a_field_filter_with_operator_in_list(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            ["bool" => ["should" => [["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface"]], ["terms" => ["values.sku-text.<all_channels>.<all_locales>" => ["sku-001"]]]]]], ["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface"]], ["terms" => ["identifier" => ["sku-001"]]]]]]], "minimum_should_match" => 1]]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::IN_LIST, ['sku-001'], null, null, []);
    }

    function it_adds_a_field_filter_with_operator_not_in_list(SearchQueryBuilder $sqb)
    {
        $sqb->addMustNot(
            ["bool" => ["should" => [["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface"]], ["terms" => ["values.sku-text.<all_channels>.<all_locales>" => ["sku-001"]]]]]], ["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface"]], ["terms" => ["identifier" => ["sku-001"]]]]]]], "minimum_should_match" => 1]]
        )->shouldBeCalled();

        $sqb->addFilter(
            ["bool" => ["should" => [["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface"]], ["exists" => ["field" => "values.sku-text.<all_channels>.<all_locales>"]]]]], ["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface"]], ["exists" => ["field" => "identifier"]]]]]], "minimum_should_match" => 1]]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::NOT_IN_LIST, ['sku-001'], null, null, []);
    }

    function it_adds_a_field_filter_with_operator_empty(SearchQueryBuilder $sqb)
    {
        $sqb->addMustNot(
            ["bool" => ["should" => [["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface"]], ["exists" => ["field" => "values.sku-text.<all_channels>.<all_locales>"]]]]], ["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface"]], ["exists" => ["field" => "identifier"]]]]]], "minimum_should_match" => 1]]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::IS_EMPTY, null, null, null, []);
    }

    function it_adds_a_field_filter_with_operator_not_empty(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            ["bool" => ["should" => [["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface"]], ["exists" => ["field" => "values.sku-text.<all_channels>.<all_locales>"]]]]], ["bool" => ["filter" => [["term" => ["document_type" => "Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface"]], ["exists" => ["field" => "identifier"]]]]]], "minimum_should_match" => 1]]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('identifier', Operators::IS_NOT_EMPTY, null, null, null, []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized(AttributeInterface $sku)
    {
        $this->shouldThrow(
            new LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['identifier', Operators::EQUALS, 'sku-001', null, null, []]);
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

    function it_throws_an_exception_when_the_given_value_is_null(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected(
                'identifier',
                IdentifierFilter::class,
                null
            )
        )->during('addFieldFilter', ['identifier', Operators::EQUALS, null, null, null, []]);
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

    function it_throws_an_exception_when_the_given_value_is_not_an_array_of_strings(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidPropertyTypeException::arrayOfStringsExpected(
                'identifier',
                IdentifierFilter::class,
                ['sku-001', null]
            )
        )->during('addFieldFilter', ['identifier', Operators::IN_LIST, ['sku-001', null], null, null, []]);
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
}
