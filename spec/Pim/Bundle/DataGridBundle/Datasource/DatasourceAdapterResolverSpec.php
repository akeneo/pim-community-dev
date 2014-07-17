<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceSupportResolver;
use Prophecy\Argument;

class DatasourceAdapterResolverSpec extends ObjectBehavior
{
    function let(DatasourceSupportResolver $supportResolver)
    {
        $supportResolver->getSupport(Argument::any())->willReturn(DatasourceSupportResolver::DATASOURCE_SUPPORT_MONGODB);
        $this->beConstructedWith($supportResolver, 'orm_adapter_class');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Datasource\DatasourceAdapterResolver');
    }

    function it_should_return_an_orm_adapter_class_when_orm_support_is_enabled($supportResolver)
    {
        $supportResolver->getSupport(Argument::any())->willReturn(DatasourceSupportResolver::DATASOURCE_SUPPORT_ORM);
        $this->getAdapterClass(Argument::any())->shouldReturn('orm_adapter_class');
    }

    function it_should_throw_an_exception_when_mongo_support_is_enabled_and_mongo_adpater_class_is_not_registered()
    {
        $this->shouldThrow()->during('getAdapterClass', [Argument::any()]);
    }

    function it_should_return_an_odm_adapter_class_for_a_smart_or_a_product_datasource()
    {
        $this->setMongodbAdapterClass('odm_adapter_class');
        $this->getAdapterClass('pim_datasource_product')->shouldReturn('odm_adapter_class');
    }
}
