<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

class StringFilterSpec extends ObjectBehavior
{
    function it_applies_on_starts_with_with_zero_value(FormFactoryInterface $factory, ProductFilterUtility $utility, FilterDatasourceAdapterInterface $ds)
    {
        $this->beConstructedWith($factory, $utility);
        $this->init('foo', [ProductFilterUtility::DATA_NAME_KEY => 'bar']);

        $utility->applyFilter($ds, 'bar', 'STARTS WITH', '0')->shouldBeCalled();
        $this->apply($ds, ['type' => 4, 'value' => '0'])->shouldReturn(true);
    }

    function it_applies_on_empty_type(FormFactoryInterface $factory, ProductFilterUtility $utility, FilterDatasourceAdapterInterface $ds)
    {
        $this->beConstructedWith($factory, $utility);
        $this->init('foo', [ProductFilterUtility::DATA_NAME_KEY => 'bar']);

        $utility->applyFilter($ds, 'bar', 'EMPTY', '')->shouldBeCalled();
        $this->apply($ds, ['type' => 'empty', 'value' => ''])->shouldReturn(true);
    }

    function it_does_not_apply_on_empty_value(FormFactoryInterface $factory, ProductFilterUtility $utility, FilterDatasourceAdapterInterface $ds)
    {
        $this->beConstructedWith($factory, $utility);
        $this->init('foo', [ProductFilterUtility::DATA_NAME_KEY => 'bar']);

        $utility->applyFilter()->shouldNotBeCalled();
        $this->apply($ds, ['type' => 3, 'value' => ''])->shouldReturn(false);
    }
}
