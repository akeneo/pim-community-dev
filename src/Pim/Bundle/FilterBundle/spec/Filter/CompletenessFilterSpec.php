<?php

namespace spec\Pim\Bundle\FilterBundle\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Component\Form\FormFactoryInterface;

class CompletenessFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\ChoiceFilter');
    }

    function it_applies_a_filter_on_complete_products(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFilter($datasource, 'completeness', Operators::AT_LEAST_COMPLETE, null)->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => BooleanFilterType::TYPE_YES]);
    }

    function it_applies_a_filter_on_not_complete_products(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFilter($datasource, 'completeness', Operators::AT_LEAST_INCOMPLETE, null)->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => BooleanFilterType::TYPE_NO]);
    }
}
