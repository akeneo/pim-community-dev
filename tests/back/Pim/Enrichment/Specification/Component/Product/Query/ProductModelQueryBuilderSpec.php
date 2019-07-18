<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductModelQueryBuilder;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class ProductModelQueryBuilderSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $repository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        CursorFactoryInterface $cursorFactory,
        SearchQueryBuilder $searchQb,
        ProductQueryBuilderOptionsResolverInterface $optionsResolver
    ) {
        $defaultContext = ['locale' => 'en_US', 'scope' => 'print'];
        $this->beConstructedWith(
            $repository,
            $filterRegistry,
            $sorterRegistry,
            $cursorFactory,
            $optionsResolver,
            $defaultContext
        );
        $optionsResolver->resolve($defaultContext)->willReturn($defaultContext);
        $this->setQueryBuilder($searchQb);
    }

    function it_is_a_product_model_query_builder()
    {
        $this->shouldImplement(ProductModelQueryBuilder::class);
    }

    function it_adds_an_entity_type_filter(
        CursorFactoryInterface $cursorFactory,
        CursorInterface $cursor,
        FieldFilterInterface $filterField,
        $filterRegistry
    )
    {
        $filterRegistry->getFieldFilter('entity_type', '=')->willReturn($filterField);
        $cursorFactory->createCursor(Argument::any(), [] )->shouldBeCalled()->willReturn($cursor);
        $filterField->setQueryBuilder(Argument::any())->shouldBeCalled();

        $filterField->addFieldFilter(
            "entity_type",
            Operators::EQUALS,
            ProductModelInterface::class,
            "en_US",
            "print",
            ["locale" => "en_US", "scope" => "print"]
        )->shouldBeCalled();

        $this->execute()->shouldReturn($cursor);

    }
}
