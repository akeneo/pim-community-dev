<?php

namespace spec\Pim\Bundle\EnrichBundle\ProductQueryBuilder;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Prophecy\Argument;

class ProductAndProductModelQueryBuilderSpec extends ObjectBehavior
{
    function let(ProductQueryBuilderInterface $pqb)
    {
        $this->beConstructedWith($pqb);
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

    function it_executes_the_query_by_adding_a_filter_on_attributes($pqb, CursorInterface $cursor)
    {
        $pqb->getRawFilters()->willReturn(
            [
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
            ]
        );

        $pqb->addFilter('attributes_for_this_level', Operators::IN_LIST, ['foo', 'baz'], [])->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $this->execute()->shouldReturn($cursor);
    }

    function it_executes_the_query_by_not_adding_a_filter_on_fields($pqb, CursorInterface $cursor)
    {
        $pqb->getRawFilters()->willReturn(
            [
                [
                    'field'    => 'bar',
                    'operator' => 'IN LIST',
                    'value'    => ['toto'],
                    'context'  => [],
                    'type'     => 'field'
                ],
            ]
        );

        $pqb->addFilter('attributes_for_this_level', Argument::cetera())->shouldNotBeCalled();
        $pqb->execute()->willReturn($cursor);

        $this->execute()->shouldReturn($cursor);
    }
}
