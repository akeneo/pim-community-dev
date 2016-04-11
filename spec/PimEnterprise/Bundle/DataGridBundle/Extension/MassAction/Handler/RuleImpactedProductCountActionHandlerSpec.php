<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Redirect\EditMassAction;
use PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RuleImpactedProductCountActionHandlerSpec extends ObjectBehavior
{
    function let(HydratorInterface $hydrator, EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($hydrator, $eventDispatcher);
    }

    function it_handles_edit_mass_action(
        $eventDispatcher,
        $hydrator,
        DatagridInterface $datagrid,
        DatasourceInterface $datasource,
        EditMassAction $massAction
    ) {
        $objectIds = ['foo', 'bar', 'baz'];

        $eventDispatcher->dispatch(
            MassActionEvents::MASS_RULE_IMPACTED_PRODUCT_COUNT_PRE_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            MassActionEvents::MASS_RULE_IMPACTED_PRODUCT_COUNT_POST_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();

        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->setHydrator($hydrator)->shouldBeCalled();
        $datasource->getResults()->willReturn($objectIds);

        $this->handle($datagrid, $massAction)->shouldReturn($objectIds);
    }
}
