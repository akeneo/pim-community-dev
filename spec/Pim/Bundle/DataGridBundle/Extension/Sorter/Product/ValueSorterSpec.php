<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Sorter\Product;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;

class ValueSorterSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface');
    }

    function it_applies_a_sort_on_product_sku(
        ProductDatasource $datasource,
        $repository,
        ProductQueryBuilderInterface $pqb,
        QueryBuilder $qb,
        AbstractAttribute $sku
    ) {
        $sku->getCode()->willReturn('sku');
        $sku->__toString()->willReturn('sku');

        $datasource->getQueryBuilder()->willReturn($qb);
        $repository->getProductQueryBuilder($qb)->willReturn($pqb);
        $pqb->addSorter('sku', 'ASC')->shouldBeCalled();

        $this->apply($datasource, $sku, 'ASC');
    }
}