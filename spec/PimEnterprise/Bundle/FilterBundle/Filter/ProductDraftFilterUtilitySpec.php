<?php

namespace spec\PimEnterprise\Bundle\FilterBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;

class ProductDraftFilterUtilitySpec extends ObjectBehavior
{
    function let(ProductDraftRepositoryInterface $productDraftRepository)
    {
        $this->beConstructedWith($productDraftRepository);
    }

    function it_applies_a_filter_on_field(
        $productDraftRepository,
        FilterDatasourceAdapterInterface $ds,
        QueryBuilder $qb
    ) {
        $ds->getQueryBuilder()->willReturn($qb);

        $productDraftRepository->applyFilter($qb, 'foo', 'bar', 'baz')->shouldBeCalled();

        $this->applyFilter($ds, 'foo', 'bar', 'baz');
    }
}
