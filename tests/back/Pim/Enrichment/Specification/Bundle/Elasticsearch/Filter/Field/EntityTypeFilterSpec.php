<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\EntityTypeFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

class EntityTypeFilterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['entity_type'], [Operators::EQUALS]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EntityTypeFilter::class);
    }

    function it_is_a_field_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    function it_supports_the_entity_type_only()
    {
        $this->supportsField('entity_type')->shouldReturn(true);
        $this->supportsField('id')->shouldReturn(false);
    }

    function it_supports_the_operators_equals_only()
    {
        $this->supportsOperator(Operators::EQUALS)->shouldReturn(true);
        $this->supportsOperator(Operators::IN_LIST)->shouldReturn(false);
    }

    function it_can_only_have_a_searchQueryBuilder_set(SearchQueryBuilder $sqb, \stdClass $notSqb)
    {
        $this->shouldNotThrow(\InvalidArgumentException::class)->during('setQueryBuilder', [$sqb]);
        $this->shouldThrow(\InvalidArgumentException::class)->during('setQueryBuilder', [$notSqb]);
    }

    function it_adds_a_filter_with_operator_equals_for_product_entities(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'document_type',
                    'query'         => str_replace('\\', '\\\\', ProductInterface::class),
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('entity_type', Operators::EQUALS, ProductInterface::class);
    }

    function it_adds_a_filter_with_operator_equals_for_product_model_entities(SearchQueryBuilder $sqb)
    {
        $sqb->addFilter(
            [
                'query_string' => [
                    'default_field' => 'document_type',
                    'query'         => str_replace('\\', '\\\\', ProductModelInterface::class),
                ],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('entity_type', Operators::EQUALS, ProductModelInterface::class);
    }

    function it_throws_if_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['entity_type', Operators::EQUALS, ProductModelInterface::class]);
    }

    function it_throws_if_the_given_field_is_not_supported(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            new \InvalidArgumentException('Unsupported field name for entity filter, only "entity_type" are supported, "invalid_property" given')
        )->during('addFieldFilter', ['invalid_property', Operators::EQUALS, ProductModelInterface::class]);
    }

    function it_throws_if_the_given_value_is_not_a_string(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidPropertyTypeException::stringExpected('entity_type', EntityTypeFilter::class, 123)
        )->during('addFieldFilter', ['entity_type', Operators::EQUALS, 123]);
    }

    function it_throws_if_the_given_operator_is_not_supported(SearchQueryBuilder $sqb)
    {
        $this->setQueryBuilder($sqb);
        $this->shouldThrow(
            InvalidOperatorException::notSupported(Operators::IN_LIST, EntityTypeFilter::class)
        )->during('addFieldFilter', ['entity_type', Operators::IN_LIST, ProductModelInterface::class]);
    }
}
