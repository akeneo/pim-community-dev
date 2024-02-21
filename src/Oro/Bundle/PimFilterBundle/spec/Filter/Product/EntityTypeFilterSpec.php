<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Oro\Bundle\PimFilterBundle\Filter\Product\EntityTypeFilter;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;

class EntityTypeFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        ProductFilterUtility $utility
    ) {
        $this->beConstructedWith($factory, $utility);
    }

    function it_is_a_product_typology_filter()
    {
        $this->shouldBeAnInstanceOf(EntityTypeFilter::class);
    }

    function it_does_not_apply_filter_on_unexpected_value(
        ProductFilterUtility $utility,
        FilterDatasourceAdapterInterface $datasource
    ) {
        $utility->applyFilter(Argument::cetera())->shouldNotBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => 'toto']);
    }

    function it_applies_filter_for_products(
        ProductFilterUtility $utility,
        FilterDatasourceAdapterInterface $datasource
    ) {
        $utility->applyFilter($datasource, 'entity_type', '=', ProductInterface::class)->shouldBeCalled();

        $this->apply($datasource, ['type' => null, 'value' => 'product']);
    }
}
