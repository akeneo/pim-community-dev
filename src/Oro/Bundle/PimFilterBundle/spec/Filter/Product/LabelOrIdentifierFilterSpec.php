<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

class LabelOrIdentifierFilterSpec extends ObjectBehavior
{
    public function let(
        FormFactoryInterface $factory,
        ProductFilterUtility $utility
    ): void {
        $this->beConstructedWith($factory, $utility);
    }

    public function it_is_a_filter(): void
    {
        $this->shouldBeAnInstanceOf(FilterInterface::class);
    }

    public function it_applies_a_filter_on_product_when_its_in_an_expected_group(
        $utility,
        FilterDatasourceAdapterInterface $datasource
    ): void {
        $utility->applyFilter($datasource, 'label_or_identifier', 'CONTAINS', 'mylabel')->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => 'mylabel']);
    }

    public function it_applies_a_filter_on_product_when_value_contains_underscore(
        $utility,
        FilterDatasourceAdapterInterface $datasource
    ): void {
        $utility->applyFilter($datasource, 'label_or_identifier', 'CONTAINS', 'mylabel_')->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => 'mylabel_']);
    }
}
