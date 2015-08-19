<?php

namespace spec\PimEnterprise\Bundle\FilterBundle\Filter\Tag;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\ProductAssetBundle\Datagrid\Filter\ProductAssetFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

class TagFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductAssetFilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);

        $this->init(
            'foo',
            [
                ProductAssetFilterUtility::DATA_NAME_KEY => 'data_name_key'
            ]
        );
    }

    function it_is_a_pim_ajax_choice_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\FilterBundle\Filter\AjaxChoiceFilter');
    }

    function it_initializes_filter_with_name()
    {
        $this->getName()->shouldreturn('foo');
    }

    function it_applies_a_filter_on_tag_field_value(
        $utility,
        FilterDatasourceAdapterInterface $datasource
    ) {
        $utility->applyTagFilter($datasource, 'data_name_key', 'IN', [1, 2])->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => [1, 2],
                'type' => 'IN'
            ]
        )->shouldReturn(true);
    }
}
