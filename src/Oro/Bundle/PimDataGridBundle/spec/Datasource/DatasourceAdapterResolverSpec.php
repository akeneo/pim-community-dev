<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Datasource;

use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceAdapterResolver;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DatasourceAdapterResolverSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('orm_adapter_class', 'product_orm_adapter_class');
        $this->addProductDatasource('pim_datasource_product');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DatasourceAdapterResolver::class);
    }

    function it_returns_an_orm_adapter_class_for_default_datasource_when_orm_support_is_enabled()
    {
        $this->getAdapterClass('pim_datasource_default')->shouldReturn('orm_adapter_class');
    }

    function it_returns_a_product_orm_adapter_class_for_product_datasource_when_orm_support_is_enabled()
    {
        $this->getAdapterClass('pim_datasource_product')->shouldReturn('product_orm_adapter_class');
    }

    function it_returns_an_orm_adapter_class_for_smart_datasource_when_orm_support_is_enabled()
    {
        $this->getAdapterClass('pim_smart_datasource')->shouldReturn('orm_adapter_class');
    }
}
