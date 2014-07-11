<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension;
use Prophecy\Argument;

class PagerResolverSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Extension\Pager\PagerResolver');
    }

    function let(PagerInterface $orm)
    {
        $this->beConstructedWith(PimCatalogExtension::DOCTRINE_MONGODB_ODM, $orm);
    }

    function it_should_return_an_orm_pager_when_orm_support_is_enabled($orm)
    {
        $this->beConstructedWith(PimCatalogExtension::DOCTRINE_ORM, $orm);
        $this->getPager(Argument::any())->shouldReturn($orm);
    }

    function it_should_throw_an_exception_when_mongo_support_is_enabled_and_mongo_pager_is_not_registered($mongo)
    {
        $this->shouldThrow()->during('getPager', [Argument::any()]);
    }

    function it_should_return_an_odm_pager_for_a_dual_or_a_product_datasource($mongo)
    {
        $this->setMongoPager($mongo);
        $this->getPager('pim_datasource_product')->shouldReturn($mongo);
    }
}
