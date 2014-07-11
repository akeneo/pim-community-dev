<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Prophecy\Argument;

class DatasourceAdapterResolverSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(PimCatalogExtension::DOCTRINE_MONGODB_ODM, 'orm_adapter_class');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Datasource\DatasourceAdapterResolver');
    }

    function it_should_return_an_orm_adapter_class_when_orm_support_is_enabled()
    {
        $this->beConstructedWith(PimCatalogExtension::DOCTRINE_ORM, 'orm_adapter_class');
        $this->getDatasourceClass(Argument::any())->shouldReturn('orm_adapter_class');
    }

    function it_should_throw_an_exception_when_mongo_support_is_enabled_and_mongo_adpater_class_is_not_registered()
    {
        $this->shouldThrow()->during('getDatasourceClass', [Argument::any()]);
    }

    function it_should_return_an_odm_adapter_class_for_a_dual_or_a_product_datasource()
    {
        $this->setOdmAdapterClass('odm_adapter_class');
        $this->getDatasourceClass('pim_datasource_product')->shouldReturn('odm_adapter_class');
    }
}
