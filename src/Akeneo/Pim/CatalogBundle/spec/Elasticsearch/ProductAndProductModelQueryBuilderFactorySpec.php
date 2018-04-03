<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\ProductQueryBuilderFactory;
use Pim\Component\Catalog\Query\ProductAndProductModelQueryBuilder;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

class ProductAndProductModelQueryBuilderFactorySpec extends ObjectBehavior
{
    function let(ProductQueryBuilderFactory $factory)
    {
        $this->beConstructedWith(ProductAndProductModelQueryBuilder::class, $factory);
    }

    function it_is_a_product_query_builder_factory()
    {
        $this->shouldImplement(ProductQueryBuilderFactoryInterface::class);
    }

    function it_creates_a_product_query_builder($factory, ProductQueryBuilderInterface $basePqb)
    {
        $factory->create(['default_locale' => 'en_US', 'default_scope' => 'print'])->willReturn($basePqb);

        $this->create(['default_locale' => 'en_US', 'default_scope' => 'print'])->shouldBeAnInstanceOf(
            ProductAndProductModelQueryBuilder::class
        );
    }
}
