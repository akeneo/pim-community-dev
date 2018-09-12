<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Filter\StringFilter;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

class LabelOrIdentifierFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        ProductFilterUtility $utility
    ) {
        $this->beConstructedWith($factory, $utility);
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf(StringFilter::class);
    }

    function it_applies_a_filter_on_product_when_its_in_an_expected_group(
        $utility,
        FilterDatasourceAdapterInterface $datasource
    ) {
        $utility->applyFilter($datasource, 'label_or_identifier', 'CONTAINS', 'toto')->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => 'toto']);
    }
}
