<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Sorter\Product;

use PhpSpec\ObjectBehavior;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ProductQueryBuilderInterface;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;

class FamilySorterSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface');
    }

    function it_applies_a_sort_on_product_family(
        ProductDatasource $datasource,
        $repository,
        ProductQueryBuilderInterface $pqb,
        QueryBuilder $qb
    ) {
        $datasource->getQueryBuilder()->willReturn($qb);
        $repository->getProductQueryBuilder($qb)->willReturn($pqb);
        $pqb->addFieldSorter('family', 'ASC')->shouldBeCalled();

        $this->apply($datasource, 'family', 'ASC');
    }
}
