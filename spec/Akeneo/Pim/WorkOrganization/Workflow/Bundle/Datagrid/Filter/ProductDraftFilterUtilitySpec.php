<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Datasource\FilterProductDatasourceAdapterInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;

class ProductDraftFilterUtilitySpec extends ObjectBehavior
{
    function let(EntityWithValuesDraftRepositoryInterface $productDraftRepository)
    {
        $this->beConstructedWith($productDraftRepository);
    }

    function it_applies_a_filter_on_field(
        FilterProductDatasourceAdapterInterface $ds,
        ProductQueryBuilderInterface $qb
    ) {
        $ds->getProductQueryBuilder()->willReturn($qb);
        $qb->addFilter('foo', 'bar', 'baz')->shouldBeCalled();

        $this->applyFilter($ds, 'foo', 'bar', 'baz');
    }
}
