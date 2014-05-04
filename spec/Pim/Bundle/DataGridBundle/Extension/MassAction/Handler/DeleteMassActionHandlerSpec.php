<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Handler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax\DeleteMassAction;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;

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
        $datasource->getRepository()->willReturn($repository);
        $datasource->getMassActionRepository()->willReturn($massActionRepo);

        // prepare mass action response
        $massAction->getOptions()->willReturn($options);
        $options->offsetGetByPath(Argument::cetera())->willReturn('qux');
    }

    function it_handles_delete_mass_action($eventDispatcher, $datasource, $massActionRepo, $datagrid, $massAction)
    {
        $objectIds    = array('foo', 'bar', 'baz');
        $countRemoved = count($objectIds);

        $datasource->getResults()->willReturn($objectIds);
        $massActionRepo->deleteFromIds($objectIds)->willReturn($countRemoved);

        $eventDispatcher->dispatch(Argument::any(), Argument::any())->shouldBeCalled();

        $this->handle($datagrid, $massAction);
    }

    function it_dispatches_events($eventDispatcher, $datasource, $massActionRepo, $datagrid, $massAction)
    {
        $objectIds    = array('foo', 'bar', 'baz');
        $countRemoved = count($objectIds);

        $datasource->getResults()->willReturn($objectIds);
        $massActionRepo->deleteFromIds($objectIds)->willReturn($countRemoved);

        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_PRE_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_POST_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();

        $this->handle($datagrid, $massAction);
    }

    function it_returns_successful_response($eventDispatcher, $datasource, $massActionRepo, $datagrid, $massAction)
    {
        $objectIds    = array('foo', 'bar', 'baz');
        $countRemoved = count($objectIds);

        $datasource->getResults()->willReturn($objectIds);
        $massActionRepo->deleteFromIds($objectIds)->willReturn($countRemoved);

        $eventDispatcher->dispatch(Argument::any(), Argument::any())->shouldBeCalled();

        $this
            ->handle($datagrid, $massAction)
            ->shouldReturnAnInstanceOf('Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface');
    }

    function it_returns_failed_message_if_an_exception_occurs(
        $eventDispatcher,
        $datasource,
        $massActionRepo,
        $datagrid,
        $massAction,
        $translator
    ) {
        $objectIds    = array('foo', 'bar', 'baz');
        $errorMessage = 'Error';

        $e = new \Exception($errorMessage);
        $translator->trans($e->getMessage())->shouldBeCalled();

        $datasource->getResults()->willReturn($objectIds);
        $massActionRepo->deleteFromIds($objectIds)->willThrow($e);

        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_PRE_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_POST_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldNotBeCalled();

        $this
            ->handle($datagrid, $massAction)
            ->shouldReturnAnInstanceOf('Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface');
    }
}
