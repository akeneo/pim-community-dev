<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ProductQueryBuilderInterface;

class EnabledFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\ChoiceFilter');
    }

    function it_applies_a_filter_on_datasource_for_enabled_field_value(
        FilterDatasourceAdapterInterface $datasource,
        $utility,
        ProductRepositoryInterface $repository,
        ProductQueryBuilderInterface $qb
    ) {
        $datasource->getQueryBuilder()->willReturn($qb);
        $utility->getProductRepository()->willReturn($repository);
        $repository->applyFilterByField($qb, 'enabled', 1)->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => [0 => 1]]);
    }
}
