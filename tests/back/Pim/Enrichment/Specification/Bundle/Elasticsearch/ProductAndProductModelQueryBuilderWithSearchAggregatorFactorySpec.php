<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelSearchAggregator;
use Akeneo\Pim\Enrichment\Bundle\ProductQueryBuilder\ProductAndProductModelQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PhpSpec\ObjectBehavior;

class ProductAndProductModelQueryBuilderWithSearchAggregatorFactorySpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $factory,
        ProductAndProductModelSearchAggregator $resultAggregator
    ) {
        $this->beConstructedWith(
            ProductAndProductModelQueryBuilder::class,
            $factory,
            $resultAggregator
        );
    }

    public function it_is_a_product_query_builder_factory()
    {
        $this->shouldImplement(ProductQueryBuilderFactoryInterface::class);
    }

    public function it_creates_a_product_and_product_model_query_builder($factory, ProductQueryBuilderInterface $basePqb)
    {
        $factory->create(['default_locale' => 'en_US', 'default_scope' => 'print'])->willReturn($basePqb);

        $this->create(['default_locale' => 'en_US', 'default_scope' => 'print'])->shouldBeAnInstanceOf(
            ProductQueryBuilderInterface::class
        );
    }
}
