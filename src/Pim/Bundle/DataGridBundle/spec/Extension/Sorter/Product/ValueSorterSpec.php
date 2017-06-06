<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Sorter\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

class ValueSorterSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface');
    }

    function it_applies_a_sort_on_product_sku(
        $attributeRepository,
        ProductDatasource $datasource,
        ProductQueryBuilderInterface $pqb,
        AttributeInterface $attribute
    ) {
        $attributeRepository->findOneByIdentifier('sku')->willReturn($attribute);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);

        $datasource->getProductQueryBuilder()->willReturn($pqb);
        $pqb->addSorter('sku', 'ASC', ['scope' => null, 'locale' => null])->shouldBeCalled();

        $this->apply($datasource, 'sku', 'ASC');
    }
}
