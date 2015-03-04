<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Sorter\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;

class ValueSorterSpec extends ObjectBehavior
{
    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface');
    }

    function it_applies_a_sort_on_product_sku(
        ProductDatasource $datasource,
        ProductQueryBuilderInterface $pqb
    ) {
        $datasource->getProductQueryBuilder()->willReturn($pqb);
        $pqb->addSorter('sku', 'ASC')->shouldBeCalled();

        $this->apply($datasource, 'sku', 'ASC');
    }
}
