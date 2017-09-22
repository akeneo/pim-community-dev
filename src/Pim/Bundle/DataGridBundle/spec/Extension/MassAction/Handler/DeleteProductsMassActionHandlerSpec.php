<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax\DeleteMassAction;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Handler\DeleteProductsMassActionHandler;
use Pim\Bundle\DataGridBundle\Normalizer\IdEncoder;
use Pim\Component\Catalog\ProductEvents;
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

    function it_dispatches_events_to_remove_products_only(
        $eventDispatcher,
        $datasource,
        $massActionRepo,
        $datagrid,
        $massAction,
        $indexRemover,
        ResultRecord $resultRecord1,
        ResultRecord $resultRecord2,
        ResultRecord $resultRecord3,
        ResultRecord $resultRecord4,
        ResultRecord $resultRecord5
    ) {
        $resultRecord1->getValue('id')->willReturn('product_model_1');
        $resultRecord1->getValue('document_type')->willReturn(IdEncoder::PRODUCT_MODEL_TYPE);

        $resultRecord2->getValue('id')->willReturn('product_1');
        $resultRecord2->getValue('document_type')->willReturn(IdEncoder::PRODUCT_TYPE);

        $resultRecord3->getValue('id')->willReturn('product_model_2');
        $resultRecord3->getValue('document_type')->willReturn(IdEncoder::PRODUCT_MODEL_TYPE);

        $resultRecord4->getValue('id')->willReturn('product_2');
        $resultRecord4->getValue('document_type')->willReturn(IdEncoder::PRODUCT_TYPE);

        $resultRecord5->getValue('id')->willReturn('product_model_3');
        $resultRecord5->getValue('document_type')->willReturn(IdEncoder::PRODUCT_MODEL_TYPE);

        $objectIds = ['product_1', 'product_2'];
        $datasource->getResults()->willReturn([
            'data' => [
                $resultRecord1,
                $resultRecord2,
                $resultRecord3,
                $resultRecord4,
                $resultRecord5,
            ],
        ]);
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

        $indexRemover->removeAll(['product_1', 'product_2'])->shouldBeCalled();

        $this->handle($datagrid, $massAction);
    }
}
