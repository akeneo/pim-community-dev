<?php

namespace spec\PimEnterprise\Bundle\FilterBundle\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;

class ProductDraftFilterUtilitySpec extends ObjectBehavior
{
    function let(ProductDraftRepositoryInterface $productDraftRepository)
    {
        $this->beConstructedWith($productDraftRepository);
    }

    function it_applies_a_filter_on_field(
        SpecFilterDatasourceAdapterInterface $ds,
        ProductQueryBuilderInterface $qb
    ) {
        $ds->getProductQueryBuilder()->willReturn($qb);
        $qb->addFilter('foo', 'bar', 'baz')->shouldBeCalled();

        $this->applyFilter($ds, 'foo', 'bar', 'baz');
    }
}

interface SpecFilterDatasourceAdapterInterface extends FilterDatasourceAdapterInterface
{
    public function getProductQueryBuilder();
}
