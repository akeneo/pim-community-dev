<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Sorter\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ProductQueryBuilderInterface;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;

class InGroupSorterSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $repository, RequestParameters $params)
    {
        $this->beConstructedWith($repository, $params);
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface');
    }

    function it_applies_a_sort_on_in_group_products(
        ProductDatasource $datasource,
        $repository,
        ProductQueryBuilderInterface $pqb,
        QueryBuilder $qb,
        $params
    ) {
        $datasource->getQueryBuilder()->willReturn($qb);
        $params->get('currentGroup', null)->willReturn(12);
        $repository->getProductQueryBuilder($qb)->willReturn($pqb);
        $pqb->addFieldSorter('in_group_12', 'ASC')->shouldBeCalled();

        $this->apply($datasource, 'in_group', 'ASC');
    }
}
