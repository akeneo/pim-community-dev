<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Handler;

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
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($hydrator, $translator, $eventDispatcher);

        $translator->trans(Argument::any())->willReturn('qux');
    }

    function it_should_handle_delete_mass_action(
        $eventDispatcher,
        $hydrator,
        DatagridInterface $datagrid,
        DeleteMassAction $massAction,
        DatasourceInterface $datasource,
        ProductRepositoryInterface $repository,
        ActionConfiguration $options
    ) {
        $objectIds    = array('foo', 'bar', 'baz');
        $countRemoved = count($objectIds);

        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->setHydrator($hydrator)->shouldBeCalled();
        $datasource->getResults()->willReturn($objectIds);
        $datasource->getRepository()->willReturn($repository);

        $repository->deleteFromIds($objectIds)->willReturn($countRemoved);

        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_PRE_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_POST_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();

        $massAction->getOptions()->willReturn($options);
        $options->offsetGetByPath(Argument::cetera())->willReturn('qux');

        $this->handle($datagrid, $massAction);
    }
}
