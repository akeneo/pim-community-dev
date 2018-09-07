<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

class FamilyFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf(ChoiceFilter::class);
    }

    function it_applies_a_filter_on_product_family(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFilter($datasource, 'family', 'IN', [2, 3])->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => [2, 3]]);
    }
}
