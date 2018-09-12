<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler;

use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Redirect\EditMassAction;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EditMassActionHandlerSpec extends ObjectBehavior
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
            MassActionEvents::MASS_EDIT_PRE_HANDLER,
            Argument::type(MassActionEvent::class)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            MassActionEvents::MASS_EDIT_POST_HANDLER,
            Argument::type(MassActionEvent::class)
        )->shouldBeCalled();

        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->setHydrator($hydrator)->shouldBeCalled();
        $datasource->getResults()->willReturn($objectIds);

        $this->handle($datagrid, $massAction)->shouldReturn($objectIds);
    }
}
