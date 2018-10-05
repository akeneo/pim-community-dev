<?php

namespace spec\Pim\Bundle\EnrichBundle\ProductQueryBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductAndProductModelQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\ProductQueryBuilder\ProductAndProductModelSearchAggregator;

class ProductAndProductModelQueryBuilderFactorySpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $factory,
        ProductAndProductModelSearchAggregator $resultAggregator
    )
    {
        $this->beConstructedWith(ProductAndProductModelQueryBuilder::class, $factory, $resultAggregator);
    }

    function it_is_a_product_query_builder_factory()
    {
        $this->shouldImplement(ProductQueryBuilderFactoryInterface::class);
    }

    function it_creates_a_product_and_product_model_query_builder($factory, ProductQueryBuilderInterface $basePqb)
    {
        $factory->create(['default_locale' => 'en_US', 'default_scope' => 'print'])->willReturn($basePqb);

        $this->create(['default_locale' => 'en_US', 'default_scope' => 'print'])->shouldBeAnInstanceOf(
            ProductAndProductModelQueryBuilder::class
        );
    }
}
