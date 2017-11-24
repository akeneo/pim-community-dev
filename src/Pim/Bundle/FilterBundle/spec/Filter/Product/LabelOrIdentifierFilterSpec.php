<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
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
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\StringFilter');
    }

    function it_applies_a_filter_on_product_when_its_in_an_expected_group(
        $utility,
        FilterDatasourceAdapterInterface $datasource
    ) {
        $utility->applyFilter($datasource, 'label_or_identifier', 'CONTAINS', 'toto')->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => 'toto']);
    }
}
