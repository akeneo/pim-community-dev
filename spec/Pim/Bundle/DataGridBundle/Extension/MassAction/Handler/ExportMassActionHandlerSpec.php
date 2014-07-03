<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Handler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Redirect\EditMassAction;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;

class ExportMassActionHandlerSpec extends ObjectBehavior
{
    function let(HydratorInterface $hydrator, EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($hydrator, $eventDispatcher);
    }

    function it_handles_export_mass_action(
        $eventDispatcher,
        $hydrator,
        DatagridInterface $datagrid,
        DatasourceInterface $datasource,
        EditMassAction $massAction
    ) {
        $objectIds = array('foo', 'bar', 'baz');

        $eventDispatcher->dispatch(
            MassActionEvents::MASS_EXPORT_PRE_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            MassActionEvents::MASS_EXPORT_POST_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();

        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->setHydrator($hydrator)->shouldBeCalled();
        $datasource->getResults()->willReturn($objectIds);

        $this->handle($datagrid, $massAction)->shouldReturn($objectIds);
    }
}
