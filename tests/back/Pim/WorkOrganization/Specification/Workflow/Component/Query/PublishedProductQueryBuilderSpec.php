<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\PublishedProductQueryBuilder;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use PhpSpec\ObjectBehavior;

class PublishedProductQueryBuilderSpec extends ObjectBehavior
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

    function it_is_initializable()
    {
        $this->shouldImplement(PublishedProductQueryBuilder::class);
    }

    function it_is_a_product_query_builder()
    {
        $this->shouldImplement(ProductQueryBuilderInterface::class);
    }
}
