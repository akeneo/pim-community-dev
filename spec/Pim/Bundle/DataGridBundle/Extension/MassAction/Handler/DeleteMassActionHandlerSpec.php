<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax\DeleteMassAction;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;

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
        ProductRepositoryInterface $repository
    ) {
        $this->beConstructedWith($hydrator, $translator, $eventDispatcher);

        $translator->trans('qux')->willReturn('qux');

        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->setHydrator($hydrator)->shouldBeCalled();
        $datasource->getRepository()->willReturn($repository);

        // prepare mass action response
        $massAction->getOptions()->willReturn($options);
        $options->offsetGetByPath(Argument::cetera())->willReturn('qux');
    }

    function it_should_handle_delete_mass_action(
        $eventDispatcher,
        $hydrator,
        $datasource,
        $repository,
        $datagrid,
        $massAction
    ) {
        $objectIds    = array('foo', 'bar', 'baz');
        $countRemoved = count($objectIds);

        $datasource->getResults()->willReturn($objectIds);
        $repository->deleteFromIds($objectIds)->willReturn($countRemoved);

        $eventDispatcher->dispatch(Argument::any(), Argument::any())->shouldBeCalled();

        $this->handle($datagrid, $massAction);
    }

    function it_should_dispatch_events(
        $eventDispatcher,
        $hydrator,
        $datasource,
        $repository,
        $datagrid,
        $massAction
    ) {
        $objectIds    = array('foo', 'bar', 'baz');
        $countRemoved = count($objectIds);

        $datasource->getResults()->willReturn($objectIds);
        $repository->deleteFromIds($objectIds)->willReturn($countRemoved);

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

    function it_should_return_successful_response(
        $eventDispatcher,
        $hydrator,
        $datasource,
        $repository,
        $datagrid,
        $massAction
    ) {
        $objectIds    = array('foo', 'bar', 'baz');
        $countRemoved = count($objectIds);

        $datasource->getResults()->willReturn($objectIds);
        $repository->deleteFromIds($objectIds)->willReturn($countRemoved);

        $eventDispatcher->dispatch(Argument::any(), Argument::any())->shouldBeCalled();

        $this
            ->handle($datagrid, $massAction)
            ->beAnInstanceOf('Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface');
    }

    function it_should_return_failed_message_if_exception_during_mass_delete(
        $eventDispatcher,
        $hydrator,
        $datasource,
        $repository,
        $datagrid,
        $massAction,
        $translator
    ) {
        $objectIds    = array('foo', 'bar', 'baz');
        $errorMessage = 'Error';

        $e = new \Exception($errorMessage);
        $translator->trans($e->getMessage())->shouldBeCalled();

        $datasource->getResults()->willReturn($objectIds);
        $repository->deleteFromIds($objectIds)->willThrow($e);

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
            ->beAnInstanceOf('Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponseInterface');
    }
}
