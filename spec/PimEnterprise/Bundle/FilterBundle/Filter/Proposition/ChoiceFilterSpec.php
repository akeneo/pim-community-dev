<?php

namespace spec\PimEnterprise\Bundle\FilterBundle\Filter\Proposition;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormFactoryInterface;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\FilterBundle\Filter\PropositionFilterUtility;

class ChoiceFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, PropositionFilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);

        $this->init(
            'foo',
            [
                PropositionFilterUtility::DATA_NAME_KEY => 'data_name_key'
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
