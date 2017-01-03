<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceSupportResolver;
use Prophecy\Argument;

class DatasourceAdapterResolverSpec extends ObjectBehavior
{
    function let(DatasourceSupportResolver $supportResolver)
    {
        $supportResolver
            ->getSupport(Argument::any())
            ->willReturn(DatasourceSupportResolver::DATASOURCE_SUPPORT_MONGODB);

        $this->beConstructedWith($supportResolver, 'orm_adapter_class', 'product_orm_adapter_class');
        $this->addProductDatasource('pim_datasource_product');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Datasource\DatasourceAdapterResolver');
    }

    function it_returns_an_orm_adapter_class_for_default_datasource_when_orm_support_is_enabled($supportResolver)
    {
        $supportResolver->getSupport('pim_datasource_default')->willReturn(DatasourceSupportResolver::DATASOURCE_SUPPORT_ORM);
        $this->getAdapterClass('pim_datasource_default')->shouldReturn('orm_adapter_class');
    }

    function it_returns_a_product_orm_adapter_class_for_product_datasource_when_orm_support_is_enabled($supportResolver)
    {
        $supportResolver->getSupport('pim_datasource_product')->willReturn(DatasourceSupportResolver::DATASOURCE_SUPPORT_ORM);
        $this->getAdapterClass('pim_datasource_product')->shouldReturn('product_orm_adapter_class');
    }

    function it_returns_an_orm_adapter_class_for_smart_datasource_when_orm_support_is_enabled($supportResolver)
    {
        $supportResolver->getSupport('pim_smart_datasource')->willReturn(DatasourceSupportResolver::DATASOURCE_SUPPORT_ORM);
        $this->getAdapterClass('pim_smart_datasource')->shouldReturn('orm_adapter_class');
    }

    function it_returns_a_product_mongo_adapter_class_for_product_datasource_when_mongo_support_is_enabled($supportResolver)
    {
        $this->setMongodbAdapterClass('odm_adapter_class');
        $this->setProductMongodbAdapterClass('product_odm_adapter_class');
        $this->getAdapterClass('pim_datasource_product')->shouldReturn('product_odm_adapter_class');
    }

    function it_returns_a_mongo_adapter_class_for_smart_datasource_when_mongo_support_is_enabled($supportResolver)
    {
        $this->setMongodbAdapterClass('odm_adapter_class');
        $this->setProductMongodbAdapterClass('product_odm_adapter_class');
        $this->getAdapterClass('pim_datasource_smart')->shouldReturn('odm_adapter_class');
    }

    function it_throws_an_exception_when_mongo_support_is_enabled_and_mongo_adpater_class_is_not_registered()
    {
        $this->shouldThrow()->during('getAdapterClass', ['foo']);
    }
}
