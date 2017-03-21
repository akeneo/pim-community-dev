<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceSupportResolver;
use Prophecy\Argument;

class DatasourceSupportResolverSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(AkeneoStorageUtilsExtension::DOCTRINE_ORM);
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Datasource\DatasourceSupportResolver');
    }

    function it_returns_an_orm_support_when_storage_driver_is_orm()
    {
        $this->beConstructedWith(AkeneoStorageUtilsExtension::DOCTRINE_ORM, []);
        $this->addSmartDatasource('grid-mongo-1');
        $this->addSmartDatasource('grid-mongo-2');
        $this->getSupport(Argument::any())->shouldReturn(DatasourceSupportResolver::DATASOURCE_SUPPORT_ORM);
    }
}
