<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Counter;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductGrid\CountImpactedProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;

class PublishedProductItemsCounterSpec extends ObjectBehavior
{
    public function let(
        CountImpactedProducts $countImpactedProducts,
        ProductQueryBuilderFactoryInterface $publishedProductQueryBuilderFactory
    ) {
        $this->beConstructedWith($countImpactedProducts, $publishedProductQueryBuilderFactory);
    }

    function it_count_from_published_product_query_factory_on_published_product_grid(
        ProductQueryBuilderFactoryInterface $publishedProductQueryBuilderFactory,
        ProductQueryBuilderInterface $queryBuilder,
        CursorInterface $cursor
    ) {
        $filters = [
            ["field" => "categories","operator" => "IN OR UNCLASSIFIED","value" => ["master","sales","tvs_projectors"]]
        ];

        $publishedProductQueryBuilderFactory->create(["filters" => $filters])->shouldBeCalled()->willReturn($queryBuilder);
        $queryBuilder->execute()->willReturn($cursor);
        $cursor->count()->willReturn(26);

        $this->count('published-product-grid', $filters)->shouldReturn(26);
    }

    function it_counts_items_in_the_product_grid(CountImpactedProducts $countImpactedProducts)
    {
        $countImpactedProducts->count(['filters'])->willReturn(42);

        $this->count('product-grid', ['filters'])->shouldReturn(42);
    }

    function it_counts_items_in_the_other_grids()
    {
        $this->count('family-grid', [
            ['value' => [1, 2, 3, 4, 5]]
        ])->shouldReturn(5);
    }

    function it_raises_an_exception_when_unable_to_count_the_number_of_items()
    {
        $this->shouldThrow(\Exception::class)->during('count', ['family-grid', ['wrong filters']]);
    }
}
