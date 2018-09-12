<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

class ProductAndProductModelCompletenessFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility)
    {
        $this->beConstructedWith($factory, $utility);
    }

    function it_is_an_oro_choice_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\ChoiceFilter');
    }

    function it_applies_a_filter_on_complete_products_and_product_models(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFilter($datasource, 'completeness', Operators::AT_LEAST_COMPLETE, null)
            ->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => BooleanFilterType::TYPE_YES]);
    }

    function it_applies_a_filter_on_not_complete_products_and_product_models(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $utility->applyFilter($datasource, 'completeness', Operators::AT_LEAST_INCOMPLETE, null)
            ->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => BooleanFilterType::TYPE_NO]);
    }
}
