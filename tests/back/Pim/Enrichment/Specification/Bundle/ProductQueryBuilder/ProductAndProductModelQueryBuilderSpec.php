<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelSearchAggregator;
use Prophecy\Argument;

class ProductAndProductModelQueryBuilderSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderInterface $pqb,
        ProductAndProductModelSearchAggregator $searchAggregator
    ) {
        $this->beConstructedWith($pqb, $searchAggregator);
    }

    function it_is_a_product_query_builder()
    {
        $this->shouldImplement(ProductQueryBuilderInterface::class);
    }

    function it_adds_a_filter($pqb)
    {
        $pqb->addFilter('id', '=', '42', [])->shouldBeCalled();
        $this->addFilter('id', '=', '42', []);
    }

    function it_adds_a_sorter($pqb)
    {
        $pqb->addSorter('sku', 'DESC', [])->shouldBeCalled();
        $this->addSorter('sku', 'DESC', []);
    }

    function it_provides_the_raw_filters($pqb)
    {
        $pqb->getRawFilters()->willReturn(['an array with awesome raw filters inside']);
        $this->getRawFilters()->shouldReturn(['an array with awesome raw filters inside']);
    }

    function it_provides_a_query_builder_once_configured($pqb, SearchQueryBuilder $searchQb)
    {
        $pqb->getQueryBuilder()->willReturn($searchQb);
        $this->getQueryBuilder()->shouldReturn($searchQb);
    }

    function it_configures_the_query_builder($pqb, SearchQueryBuilder $searchQb)
    {
        $pqb->setQueryBuilder($searchQb)->willReturn($this);
        $this->setQueryBuilder($searchQb)->shouldReturn($this);
    }

    function it_executes_the_query_and_aggregate_results(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'foo',
                'operator' => 'CONTAINS',
                'value'    => '42',
                'context'  => [],
                'type'     => 'attribute'
            ],
            [
                'field'    => 'bar',
                'operator' => 'IN LIST',
                'value'    => ['toto'],
                'context'  => [],
                'type'     => 'field'
            ],
            [
                'field'    => 'baz',
                'operator' => 'EQUALS',
                'value'    => 'sku_893042',
                'context'  => [],
                'type'     => 'attribute'
            ],
        ];

        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('parent', Operators::IS_EMPTY, null, [])->shouldNotBeCalled();

        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldBeCalled();

        $this->execute()->shouldReturn($cursor);
    }

    function it_executes_the_query_by_adding_a_filter_on_attributes_and_categories(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters =[
            [
                'field'    => 'categories',
                'operator' => 'IN OR UNCLASSIFIED',
                'value'    => ['toto'],
                'context'  => [],
                'type'     => 'field'
            ]
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('parent', Operators::IS_EMPTY, null, [])->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldBeCalled();

        $this->execute()->shouldReturn($cursor);
    }

    function it_executes_the_query_with_operator_is_empty_on_an_attribute(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'foo',
                'operator' => 'EMPTY',
                'value'    => null,
                'context'  => [],
                'type'     => 'attribute',
            ],
            [
                'field'    => 'foo_currency1',
                'operator' => 'EMPTY FOR CURRENCY',
                'value'    => null,
                'context'  => [],
                'type'     => 'attribute',
            ],
            [
                'field'    => 'foo_currency2',
                'operator' => 'EMPTY ON ALL CURRENCIES',
                'value'    => null,
                'context'  => [],
                'type'     => 'attribute',
            ],
            [
                'field'    => 'bar',
                'operator' => 'IN',
                'value'    => ['toto'],
                'context'  => [],
                'type'     => 'field',
            ],
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['category_A'],
                'context'  => [],
                'type'     => 'field',
            ],
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('parent', Operators::IS_EMPTY, null, [])->shouldNotBeCalled();
        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldBeCalled();

        $this->execute()->shouldReturn($cursor);
    }

    function it_does_not_add_a_default_filter_on_parents_when_there_is_a_source_attribute_filter(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'bar',
                'operator' => 'IN LIST',
                'value'    => ['toto'],
                'context'  => [],
                'type'     => 'attribute'
            ],
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('parent', Argument::cetera())->shouldNotBeCalled();
        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldBeCalled();

        $this->execute()->shouldReturn($cursor);
    }

    function it_does_not_add_a_default_filter_on_parents_when_there_is_a_source_parent_filter(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'parent',
                'operator' => 'IN LIST',
                'value'    => ['toto'],
                'context'  => [],
                'type'     => 'field'
            ]
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('parent', Argument::cetera())->shouldNotBeCalled();
        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldNotBeCalled();

        $this->execute()->shouldReturn($cursor);
    }

    function it_does_not_add_a_default_filter_on_parents_nor_group_when_there_is_a_filter_on_enabled(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'enabled',
                'operator' => '=',
                'value'    => true,
                'context'  => [],
                'type'     => 'field'
            ]
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('entity_type', '=', ProductInterface::class, Argument::cetera())->shouldBeCalled();
        $pqb->addFilter('parent', Argument::cetera())->shouldNotBeCalled();
        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldNotBeCalled();

        $this->execute()->shouldReturn($cursor);
    }

    function it_does_not_add_a_default_filter_on_parents_when_there_is_a_source_id_filter(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'id',
                'operator' => 'IN LIST',
                'value'    => ['toto'],
                'context'  => [],
                'type'     => 'field'
            ],
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('parent', Argument::cetera())->shouldNotBeCalled();
        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldNotBeCalled();

        $this->execute()->shouldReturn($cursor);
    }

    function it_does_not_add_a_default_filter_on_parents_when_there_is_a_source_identifier_filter(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'identifier',
                'operator' => 'IN LIST',
                'value'    => ['toto'],
                'context'  => [],
                'type'     => 'field'
            ],
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('parent', Argument::cetera())->shouldNotBeCalled();
        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldBeCalled();

        $this->execute()->shouldReturn($cursor);
    }

    function it_does_not_add_a_default_filter_on_parents_when_there_is_a_source_entity_type_filter(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'entity_type',
                'operator' => 'EQUALS',
                'value'    => 'toto',
                'context'  => [],
                'type'     => 'field'
            ],
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('parent', Argument::cetera())->shouldNotBeCalled();
        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldBeCalled();

        $this->execute()->shouldReturn($cursor);
    }

    function it_does_not_add_a_default_filter_on_parents_when_there_is_a_source_ancestor_filter(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'ancestor.id',
                'operator' => 'IN LIST',
                'value'    => ['toto'],
                'context'  => [],
                'type'     => 'field'
            ],
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('parent', Argument::cetera())->shouldNotBeCalled();
        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldBeCalled();

        $this->execute()->shouldReturn($cursor);
    }

    function it_does_not_add_a_default_filter_on_parents_when_there_is_a_source_ancestor_or_self_filter(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'self_and_ancestor.id',
                'operator' => 'IN LIST',
                'value'    => ['toto'],
                'context'  => [],
                'type'     => 'field'
            ],
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('parent', Argument::cetera())->shouldNotBeCalled();
        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldBeCalled();

        $this->execute()->shouldReturn($cursor);
    }

    function it_does_not_add_a_default_filter_on_parents_when_there_is_a_filter_on_category_with_operator_IN_LIST(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['category_A', 'category_B'],
                'context'  => [],
                'type'     => 'field'
            ],
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('parent', Argument::cetera())->shouldNotBeCalled();
        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldBeCalled();

        $this->execute()->shouldReturn($cursor);
    }

    function it_does_not_add_a_default_filter_on_parents_when_there_is_a_filter_on_category_with_operator_IN_CHILDREN(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'categories',
                'operator' => 'IN CHILDREN',
                'value'    => ['category_A', 'category_B'],
                'context'  => [],
                'type'     => 'field'
            ],
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('parent', Argument::cetera())->shouldNotBeCalled();
        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldBeCalled();

        $this->execute()->shouldReturn($cursor);
    }

    function it_does_not_aggregate_when_there_is_a_filter_on_parent(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'parent',
                'operator' => '=',
                'value'    => 'model-tshirt-divided-blue',
                'context'  => [],
                'type'     => 'field'
            ],
            [
                'field'    => 'foo',
                'operator' => 'CONTAINS',
                'value'    => '42',
                'context'  => [],
                'type'     => 'attribute'
            ],
            [
                'field'    => 'categories',
                'operator' => 'IN LIST',
                'value'    => ['category_A', 'category_'],
                'context'  => [],
                'type'     => 'field'
            ]
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $sqb->addFilter(Argument::cetera())->shouldNotBeCalled();

        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldNotBeCalled();
        $this->execute()->shouldReturn($cursor);
    }

    function it_does_not_aggregate_when_there_is_a_filter_on_id(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'id',
                'operator' => 'IN',
                'value'    => ['product_41', 'product_42'],
                'context'  => [],
                'type'     => 'field'
            ],
            [
                'field'    => 'foo',
                'operator' => 'CONTAINS',
                'value'    => '42',
                'context'  => [],
                'type'     => 'attribute'
            ],
        ];
        $pqb->getRawFilters()->willReturn($rawFilters);

        $sqb->addFilter(Argument::cetera())->shouldNotBeCalled();

        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldNotBeCalled();
        $this->execute()->shouldReturn($cursor);
    }

    function it_does_not_add_a_default_filter_on_parents_when_there_is_a_filter_on_groups(
        $pqb,
        $searchAggregator,
        CursorInterface $cursor,
        SearchQueryBuilder $sqb
    ) {
        $rawFilters = [
            [
                'field'    => 'groups',
                'operator' => 'IN',
                'value'    => ['group_A', 'group_B'],
                'context'  => [],
                'type'     => 'field'
            ],
        ];

        $pqb->getRawFilters()->willReturn($rawFilters);

        $pqb->addFilter('parent', Argument::cetera())->shouldNotBeCalled();
        $pqb->execute()->willReturn($cursor);
        $pqb->getQueryBuilder()->willReturn($sqb);
        $searchAggregator->aggregateResults($sqb, $rawFilters)->shouldBeCalled();

        $this->execute()->shouldReturn($cursor);
    }
}
