<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter\Product;

use Oro\Bundle\PimFilterBundle\Filter\Product\ProductTypologyFilter;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;

class ProductTypologyFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        ProductFilterUtility $utility
    ) {
        $this->beConstructedWith($factory, $utility);
    }

    function it_is_a_product_typology_filter()
    {
        $this->shouldBeAnInstanceOf(ProductTypologyFilter::class);
    }

    function it_does_not_apply_filter_on_unexpected_value(
        ProductFilterUtility $utility,
        FilterDatasourceAdapterInterface $datasource
    ) {
        $utility->applyFilter(Argument::cetera())->shouldNotBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => 'toto']);
    }

    function it_applies_filter_for_simple_product_typology(
        ProductFilterUtility $utility,
        FilterDatasourceAdapterInterface $datasource
    ) {
        $utility->applyFilter($datasource, 'family_variant', 'EMPTY', null)->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => 'simple']);
    }

    function it_applies_filter_for_variant_product_typology(
        ProductFilterUtility $utility,
        FilterDatasourceAdapterInterface $datasource
    ) {
        $utility->applyFilter($datasource, 'family_variant', 'NOT EMPTY', null)->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => 'variant']);
    }
}
