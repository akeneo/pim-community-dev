<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

class EnabledFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf(ChoiceFilter::class);
    }

    function it_applies_a_filter_on_enabled_field_value(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFilter($datasource, 'enabled', '=', true)->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => [0 => true]]);
    }
}
