<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Datagrid\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Datagrid\Filter\ProductDraftFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

class ChoiceFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductDraftFilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);

        $this->init(
            'foo',
            [
                ProductDraftFilterUtility::DATA_NAME_KEY => 'data_name_key'
            ]
        );
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\ChoiceFilter');
    }

    function it_initializes_filter_with_name()
    {
        $this->getName()->shouldreturn('foo');
    }

    function it_applies_a_filter_on_status_field_value(
        $utility,
        FilterDatasourceAdapterInterface $datasource
    ) {
        $utility->applyFilter($datasource, 'data_name_key', 'IN', [1, 2])->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => [1, 2],
                'type' => 'IN'
            ]
        )->shouldReturn(true);
    }
}
