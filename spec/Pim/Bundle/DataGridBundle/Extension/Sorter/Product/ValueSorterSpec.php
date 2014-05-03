<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Sorter\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ProductQueryBuilderInterface;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;

class ValueSorterSpec extends ObjectBehavior
{
    function let(ProductRepositoryInterface $repository, AttributeRepository $attributeRepository)
    {
        $this->beConstructedWith($repository, $attributeRepository);
    }

    function it_should_be_a_sorter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DataGridBundle\Extension\Sorter\SorterInterface');
    }

    function it_applies_a_sort_on_product_sku(
        ProductDatasource $datasource,
        ProductRepositoryInterface $repository,
        ProductQueryBuilderInterface $pqb,
        QueryBuilder $qb,
        CustomAttributeRepository $attributeRepository,
        AbstractAttribute $sku
    ) {
        // TODO : how to mock the following magic method ?
        $attributeRepository->findOneByCode('sku')->willReturn($sku);
        $sku->getCode()->willReturn('sku');
        $sku->__toString()->willReturn('sku');

        $datasource->getQueryBuilder()->willReturn($qb);
        $repository->getProductQueryBuilder($qb)->willReturn($pqb);
        $pqb->addAttributeSorter($sku, 'ASC')->shouldBeCalled();

        $this->apply($datasource, $sku, 'ASC');
    }
}

class CustomAttributeRepository extends AttributeRepository
{
    public function findOneByCode()
    {
        return null;
    }
}
