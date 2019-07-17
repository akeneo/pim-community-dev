<?php


namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\AbstractEntityWithValuesQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use PhpSpec\ObjectBehavior;

class AbstractEntityWithValuesQueryBuilderSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        CursorFactoryInterface $cursorFactory,
        ProductQueryBuilderOptionsResolverInterface $optionResolver
    ) {
        $defaultContext = ['locale' => 'en_US', 'scope' => 'print'];

        $this->beConstructedWith(
            $attributeRepository,
            $filterRegistry,
            $sorterRegistry,
            $cursorFactory,
            $defaultContext
        );
        $optionResolver->resolve($defaultContext)->willReturn($defaultContext);
    }

    function it_is_a_product_query_builder()
    {
        $this->shouldImplement(AbstractEntityWithValuesQueryBuilder::class);
    }

}
