<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Sorter\Product;

use Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;

class CompletenessSorterSpec extends ObjectBehavior
{
    function it_is_a_sorter()
    {
        $this->shouldImplement(SorterInterface::class);
    }

    function it_applies_a_sort_on_product_completeness(
        ProductDatasource $datasource,
        ProductQueryBuilderInterface $pqb
    ) {
        $datasource->getProductQueryBuilder()->willReturn($pqb);
        $pqb->addSorter('completeness', 'ASC')->shouldBeCalled();

        $this->apply($datasource, 'completeness', 'ASC');
    }
}
