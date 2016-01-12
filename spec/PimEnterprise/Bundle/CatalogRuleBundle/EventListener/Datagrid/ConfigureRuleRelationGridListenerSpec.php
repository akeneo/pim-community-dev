<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\EventListener\Datagrid;

use Akeneo\Bundle\RuleEngineBundle\Doctrine\ORM\QueryBuilder\RuleQueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\Datasource;
use Prophecy\Argument;

class ConfigureRuleRelationGridListenerSpec extends ObjectBehavior
{
    function let(RequestParameters $requestParams)
    {
        $this->beConstructedWith($requestParams, 'RuleRelationClass');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\EventListener\Datagrid\ConfigureRuleRelationGridListener');
    }

    function it_doesnt_configure_if_no_params(
        BuildAfter $event,
        DatagridInterface $datagrid,
        Datasource $datasource,
        RuleQueryBuilder $qb
    ) {
        $event->getDatagrid()->willReturn($datagrid);
        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->getQueryBuilder()->willReturn($qb);

        $qb->joinResource(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->configure($event);
    }

    function it_configures_the_query_builder(
        $requestParams,
        BuildAfter $event,
        DatagridInterface $datagrid,
        Datasource $datasource,
        RuleQueryBuilder $qb
    ) {
        $event->getDatagrid()->willReturn($datagrid);
        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->getQueryBuilder()->willReturn($qb);

        $requestParams->get('resourceName', null)->willReturn('Pim\Bundle\CatalogBundle\Entity\Attribute');
        $requestParams->get('resourceName')->willReturn('Pim\Bundle\CatalogBundle\Entity\Attribute');
        $requestParams->get('resourceId')->willReturn(35);

        $qb->joinResource('Pim\Bundle\CatalogBundle\Entity\Attribute', 35)->shouldBeCalled();

        $this->configure($event);
    }
}
