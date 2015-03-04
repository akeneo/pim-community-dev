<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceSupportResolver;
use Prophecy\Argument;

class PagerResolverSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\DataGridBundle\Extension\Pager\PagerResolver');
    }

    function let(DatasourceSupportResolver $supportResolver, PagerInterface $orm)
    {
        $supportResolver
            ->getSupport(Argument::any())
            ->willReturn(DatasourceSupportResolver::DATASOURCE_SUPPORT_MONGODB);
        $this->beConstructedWith($supportResolver, $orm);
    }

    function it_returns_an_orm_pager_when_orm_support_is_enabled($supportResolver, $orm)
    {
        $supportResolver->getSupport('orm')->willReturn(DatasourceSupportResolver::DATASOURCE_SUPPORT_ORM);
        $this->getPager('orm')->shouldReturn($orm);
    }

    function it_throws_an_exception_when_mongo_support_is_enabled_and_mongo_pager_is_not_registered()
    {
        $this->shouldThrow()->during('getPager', [Argument::any()]);
    }

    function it_returns_an_odm_pager_for_a_smart_or_a_product_datasource(PagerInterface $mongo)
    {
        $this->setMongodbPager($mongo);
        $this->getPager('pim_datasource_product')->shouldReturn($mongo);
    }
}
