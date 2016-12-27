<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Filter;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

class ProductAssetFilterUtilitySpec extends ObjectBehavior
{
    function let(AssetRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_can_filter_by_tag()
    {
        $this->shouldImplement('PimEnterprise\Bundle\FilterBundle\Filter\Tag\TagFilterAwareInterface');
    }

    function it_applies_a_tag_filter(
        $repository,
        FilterDatasourceAdapterInterface $ds,
        QueryBuilder $qb
    ) {
        $ds->getQueryBuilder()->willReturn($qb);

        $repository->applyTagFilter($qb, 'foo', 'bar', 'baz')->shouldBeCalled();

        $this->applyTagFilter($ds, 'foo', 'bar', 'baz');
    }

    function it_applies_a_filter(
        $repository,
        FilterDatasourceAdapterInterface $ds,
        QueryBuilder $qb
    ) {
        $ds->getQueryBuilder()->willReturn($qb);

        $repository->applyCategoriesFilter($qb, Operators::IN_LIST_OR_UNCLASSIFIED, ['foo', 'bar'])->shouldBeCalled();

        $this->applyFilter($ds, 'categories', Operators::IN_LIST_OR_UNCLASSIFIED,  ['foo', 'bar']);
    }
}
