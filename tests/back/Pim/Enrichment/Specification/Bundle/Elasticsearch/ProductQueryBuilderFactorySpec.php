<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class ProductQueryBuilderFactorySpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attRepository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        CursorFactoryInterface $cursorFactory,
        ProductQueryBuilderOptionsResolverInterface $optionsResolver
    ) {
        $this->beConstructedWith(
            ProductQueryBuilder::class,
            $attRepository,
            $filterRegistry,
            $sorterRegistry,
            $cursorFactory,
            $optionsResolver
        );
    }

    function it_is_a_product_query_factory()
    {
        $this->shouldImplement(ProductQueryBuilderFactoryInterface::class);
    }

    function it_creates_a_product_query_builder()
    {
        $pqb = $this->create(['default_locale' => 'en_US', 'default_scope' => 'print']);
        $pqb->getQueryBuilder()->shouldBeAnInstanceOf(SearchQueryBuilder::class);
        $pqb->shouldBeAnInstanceOf(ProductQueryBuilder::class);
    }

    function it_creates_a_product_query_builder_with_filters(
        $attRepository,
        $filterRegistry,
        $optionsResolver,
        FieldFilterInterface $filter
    ) {
        $attRepository->findOneByIdentifier('family')->willReturn(null);
        $filterRegistry->getFieldFilter('family', 'CONTAINS')->willReturn($filter);
        $optionsResolver->resolve(Argument::any())->willReturn(['locale' => 'en_US', 'scope' => 'print']);

        $pqb = $this->create(
            [
                'default_locale' => 'en_US',
                'default_scope'  => 'print',
                'filters'        => [
                    [
                        'field'    => 'family',
                        'operator' => 'CONTAINS',
                        'value'    => 'foo'
                    ],
                ]
            ]
        );

        $expectedRawFilter = [
            'field'    => 'family',
            'operator' => 'CONTAINS',
            'value'    => 'foo',
            'context'  => ['locale' => 'en_US', 'scope' => 'print'],
            'type'     => 'field'
        ];

        $pqb->getRawFilters()->shouldHaveCount(1);
        $pqb->getRawFilters()->shouldHaveKeyWithValue(0, $expectedRawFilter);
    }
}
