<?php

namespace spec\PimEnterprise\Bundle\FilterBundle\Filter\ProductDraft;

use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\CatalogBundle\Query\Filter\Operators;
use PimEnterprise\Bundle\FilterBundle\Filter\ProductDraftFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

class AttributeChoiceFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductDraftFilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);

        $this->init('foo', [ProductDraftFilterUtility::DATA_NAME_KEY => 'data_name_key']);
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\ChoiceFilter');
    }

    function it_applies_custom_operator_with_default_type($utility, FilterDatasourceAdapterInterface $ds)
    {
        $utility->applyFilter($ds, 'data_name_key', Operators::IN_ARRAY_KEYS, ['bar'])->shouldBeCalled();

        $this->apply($ds, ['value' => 'bar'])->shouldReturn(true);
    }

    function it_applies_empty_operator_with_type_empty($utility, FilterDatasourceAdapterInterface $ds)
    {
        $utility->applyFilter($ds, 'data_name_key', Operators::IS_EMPTY, ['null'])->shouldBeCalled();

        $this->apply($ds, ['type' => FilterType::TYPE_EMPTY, 'value' => 'null'])->shouldReturn(true);
    }
}
