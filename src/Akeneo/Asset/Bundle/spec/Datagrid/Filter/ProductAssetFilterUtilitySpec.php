<?php

namespace spec\Akeneo\Asset\Bundle\Datagrid\Filter;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Asset\Bundle\Datagrid\Filter\TagFilterAwareInterface;
use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;

class ProductAssetFilterUtilitySpec extends ObjectBehavior
{
    function let(AssetRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_can_filter_by_tag()
    {
        $this->shouldImplement(TagFilterAwareInterface::class);
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
