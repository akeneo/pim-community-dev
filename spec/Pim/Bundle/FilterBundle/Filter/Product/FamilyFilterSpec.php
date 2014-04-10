<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ProductQueryBuilderInterface;

class FamilyFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\ChoiceFilter');
    }

    function it_applies_a_filter_on_product_family(
        FilterDatasourceAdapterInterface $datasource,
        $utility,
        ProductRepositoryInterface $repository,
        ProductQueryBuilderInterface $pqb,
        QueryBuilder $qb
    ) {
        $datasource->getQueryBuilder()->willReturn($qb);
        $utility->getProductRepository()->willReturn($repository);
        $repository->getProductQueryBuilder($qb)->willReturn($pqb);
        $pqb->addFieldFilter('family', 'IN', [2, 3])->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => [2, 3]]);
    }
}
