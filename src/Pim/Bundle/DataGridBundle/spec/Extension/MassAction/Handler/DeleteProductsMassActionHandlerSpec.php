<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\ProductEvents;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax\DeleteMassAction;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Pim\Component\Catalog\Repository\ProductMassActionRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Translation\TranslatorInterface;

class DeleteProductsMassActionHandlerSpec extends ObjectBehavior
{
    function let(
        HydratorInterface $hydrator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        DatagridInterface $datagrid,
        DatasourceInterface $datasource,
        DeleteMassAction $massAction,
        ActionConfiguration $options,
        ProductMassActionRepositoryInterface $massActionRepo,
        BulkRemoverInterface $indexRemover
    ) {
        $this->beConstructedWith(
            $hydrator,
            $translator,
            $eventDispatcher,
            $indexRemover
        );

        $translator->trans('qux')->willReturn('qux');

        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->setHydrator($hydrator)->shouldBeCalled();
        $datasource->getMassActionRepository()->willReturn($massActionRepo);

        // prepare mass action response
        $massAction->getOptions()->willReturn($options);
        $options->offsetGetByPath(Argument::cetera())->willReturn('qux');
    }

    function it_dispatches_events(
        $eventDispatcher,
        $datasource,
        $massActionRepo,
        $datagrid,
        $massAction,
        $indexRemover,
        ResultRecord $resultRecord
    ) {
        $resultRecord->getValue('id')->willReturn('foo');
        $objectIds = ['foo'];
        $datasource->getResults()->willReturn(['data' => [$resultRecord]]);
        $massActionRepo->deleteFromIds($objectIds)->willReturn(1);

        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_PRE_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_POST_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            ProductEvents::PRE_MASS_REMOVE,
            new GenericEvent($objectIds)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            ProductEvents::POST_MASS_REMOVE,
            new GenericEvent($objectIds)
        )->shouldBeCalled();

        $indexRemover->removeAll(['foo'])->shouldBeCalled();

        $this->handle($datagrid, $massAction);
    }
}
