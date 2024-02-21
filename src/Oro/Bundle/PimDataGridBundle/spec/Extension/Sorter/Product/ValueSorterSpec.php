<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\Sorter\Product;

use Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

class ValueSorterSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_a_sorter()
    {
        $this->shouldImplement(SorterInterface::class);
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
