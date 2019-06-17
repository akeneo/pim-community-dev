<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductAndProductModelQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;

class ProductAndProductModelQueryBuilderFactorySpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        CursorFactoryInterface $cursorFactory,
        ProductQueryBuilderOptionsResolverInterface $optionsResolver
    )
    {
        $this->beConstructedWith(
            ProductAndProductModelQueryBuilder::class,
            $attributeRepository,
            $filterRegistry,
            $sorterRegistry,
            $cursorFactory,
            $optionsResolver
        );
    }

    function it_is_a_product_query_builder_factory()
    {
        $this->shouldImplement(ProductQueryBuilderFactoryInterface::class);
    }

    function it_creates_a_product_query_builder()
    {
        $this->create(['default_locale' => 'en_US', 'default_scope' => 'print'])->shouldBeAnInstanceOf(
            ProductAndProductModelQueryBuilder::class
        );
    }
}
