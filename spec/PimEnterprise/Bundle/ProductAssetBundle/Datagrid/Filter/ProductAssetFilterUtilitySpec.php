<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Filter;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\CategoryFilter;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

class ProductAssetFilterUtilitySpec extends ObjectBehavior
{
    function let(AssetRepositoryInterface $assetRepositoryInterface, CategoryFilter $categoryFilter)
    {
        $this->beConstructedWith($assetRepositoryInterface, $categoryFilter);
    }

    function it_can_filter_by_tag()
    {
        $this->shouldImplement('PimEnterprise\Bundle\FilterBundle\Filter\Tag\TagFilterAwareInterface');
    }

    function it_applies_a_tag_filter(
        $assetRepositoryInterface,
        FilterDatasourceAdapterInterface $ds,
        QueryBuilder $qb
    ) {
        $ds->getQueryBuilder()->willReturn($qb);

        $assetRepositoryInterface->applyTagFilter($qb, 'foo', 'bar', 'baz')->shouldBeCalled();

        $this->applyTagFilter($ds, 'foo', 'bar', 'baz');
    }
}
