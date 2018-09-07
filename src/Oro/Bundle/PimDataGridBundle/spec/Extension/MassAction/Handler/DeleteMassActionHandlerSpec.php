<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Handler;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Ajax\DeleteMassAction;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductMassActionRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DeleteMassActionHandlerSpec extends ObjectBehavior
{
    function let(
        HydratorInterface $hydrator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        DatagridInterface $datagrid,
        DatasourceInterface $datasource,
        DeleteMassAction $massAction,
        ActionConfiguration $options,
        ProductRepositoryInterface $repository,
        ProductMassActionRepositoryInterface $massActionRepo
    ) {
        $this->beConstructedWith($hydrator, $translator, $eventDispatcher);

        $translator->trans('qux')->willReturn('qux');

        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->setHydrator($hydrator)->shouldBeCalled();
        $datasource->getMassActionRepository()->willReturn($massActionRepo);

        // prepare mass action response
        $massAction->getOptions()->willReturn($options);
        $options->offsetGetByPath(Argument::cetera())->willReturn('qux');
    }

    function it_handles_delete_mass_action($eventDispatcher, $datasource, $massActionRepo, $datagrid, $massAction)
    {
        $objectIds = ['foo', 'bar', 'baz'];
        $countRemoved = count($objectIds);

        $datasource->getResults()->willReturn($objectIds);
        $massActionRepo->deleteFromIds($objectIds)->willReturn($countRemoved);

        $eventDispatcher->dispatch(Argument::any(), Argument::any())->shouldBeCalled();

        $this->handle($datagrid, $massAction);
    }

    function it_dispatches_events($eventDispatcher, $datasource, $massActionRepo, $datagrid, $massAction)
    {
        $objectIds = ['foo', 'bar', 'baz'];
        $countRemoved = count($objectIds);

        $datasource->getResults()->willReturn($objectIds);
        $massActionRepo->deleteFromIds($objectIds)->willReturn($countRemoved);

        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_PRE_HANDLER,
            Argument::type(MassActionEvent::class)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_POST_HANDLER,
            Argument::type(MassActionEvent::class)
        )->shouldBeCalled();

        $this->handle($datagrid, $massAction);
    }

    function it_returns_successful_response($eventDispatcher, $datasource, $massActionRepo, $datagrid, $massAction)
    {
        $objectIds = ['foo', 'bar', 'baz'];
        $countRemoved = count($objectIds);

        $datasource->getResults()->willReturn($objectIds);
        $massActionRepo->deleteFromIds($objectIds)->willReturn($countRemoved);

        $eventDispatcher->dispatch(Argument::any(), Argument::any())->shouldBeCalled();

        $this
            ->handle($datagrid, $massAction)
            ->shouldReturnAnInstanceOf(MassActionResponseInterface::class);
    }

    function it_returns_failed_message_if_an_exception_occurs(
        $eventDispatcher,
        $datasource,
        $massActionRepo,
        $datagrid,
        $massAction,
        $translator
    ) {
        $objectIds = ['foo', 'bar', 'baz'];
        $errorMessage = 'Error';

        $e = new \Exception($errorMessage);
        $translator->trans($e->getMessage())->shouldBeCalled();

        $datasource->getResults()->willReturn($objectIds);
        $massActionRepo->deleteFromIds($objectIds)->willThrow($e);

        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_PRE_HANDLER,
            Argument::type(MassActionEvent::class)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_POST_HANDLER,
            Argument::type(MassActionEvent::class)
        )->shouldNotBeCalled();

        $this
            ->handle($datagrid, $massAction)
            ->shouldReturnAnInstanceOf(MassActionResponseInterface::class);
    }
}
