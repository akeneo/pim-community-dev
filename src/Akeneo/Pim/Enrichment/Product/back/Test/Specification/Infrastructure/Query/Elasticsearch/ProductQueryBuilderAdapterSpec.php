<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface;
use Akeneo\Pim\Enrichment\Product\Domain\PQB\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\Elasticsearch\ProductQueryBuilderAdapter;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;

class ProductQueryBuilderAdapterSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        ProductQueryBuilderOptionsResolverInterface $optionResolver
    ) {
        $optionResolver->resolve(['locale' => null, 'scope'  => null])->willReturn(['locale' => null, 'scope'  => null]);
        $this->beConstructedWith($attributeRepository, $filterRegistry, $sorterRegistry, $optionResolver);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductQueryBuilderAdapter::class);
        $this->shouldImplement(ProductQueryBuilderInterface::class);
    }

    function it_builds_the_query(
        FilterRegistryInterface $filterRegistry,
        FieldFilterInterface $fieldFilter
    ) {
        $filterRegistry->getFieldFilter('entity_type', Operators::EQUALS)->willReturn($fieldFilter);

        $this->buildQuery()->shouldReturn(['_source' => ['identifier']]);
    }
}
