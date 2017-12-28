<?php

namespace spec\Pim\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Ajax\DeleteMassAction;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\ProductEvents;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
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
        ProductDatasource $datasource,
        BulkRemoverInterface $indexRemover,
        CursorFactoryInterface $cursorFactory,
        ProductQueryBuilderInterface $pqb,
        SearchQueryBuilder $qb,
        CursorInterface $productCursor
    ) {
        $this->beConstructedWith(
            $hydrator,
            $translator,
            $eventDispatcher,
            $indexRemover,
            $cursorFactory
        );

        $datasource->getProductQueryBuilder()->willReturn($pqb);
        $pqb->getQueryBuilder()->willReturn($qb);
        $qb->getQuery()->willReturn([]);

        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->setHydrator($hydrator)->shouldBeCalled();

        $cursorFactory->createCursor([])->willReturn($productCursor);

        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_PRE_HANDLER,
            Argument::type(MassActionEvent::class)
        )->shouldBeCalled();
    }

    function it_dispatches_events_to_remove_products_only(
        $eventDispatcher,
        $datasource,
        $datagrid,
        $indexRemover,
        $productCursor,
        ActionConfiguration $options,
        DeleteMassAction $massAction,
        ProductMassActionRepositoryInterface $massActionRepo,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3
    ) {
        $datasource->getMassActionRepository()->willReturn($massActionRepo);
        // prepare mass action response
        $massAction->getOptions()->willReturn($options);
        $options->offsetGetByPath(Argument::cetera())->willReturn('qux');

        $product1->getId()->willReturn(42);
        $product2->getId()->willReturn(56);
        $product3->getId()->willReturn(91);

        $productCursor->count()->willReturn(3);
        $productCursor->rewind()->shouldBeCalled();
        $productCursor->valid()->shouldBeCalled()->willReturn(true, true, true, false);
        $productCursor->next()->shouldBeCalled();
        $productCursor->current()->willReturn($product1, $product2, $product3);

        $objectIds = [42,56,91];

        $massActionRepo->deleteFromIds($objectIds)->shouldBeCalled()->willReturn(3);
        $indexRemover->removeAll($objectIds)->shouldBeCalled();

        $eventDispatcher->dispatch(
            MassActionEvents::MASS_DELETE_POST_HANDLER,
            Argument::type(MassActionEvent::class)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            ProductEvents::PRE_MASS_REMOVE,
            new GenericEvent($objectIds)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            ProductEvents::POST_MASS_REMOVE,
            new GenericEvent($objectIds)
        )->shouldBeCalled();


        $this->handle($datagrid, $massAction);
    }

    function it_does_not_run_if_max_limit_is_exceeded(
        $datagrid,
        $translator,
        $productCursor,
        MassActionInterface $massAction

    ) {
        $productCursor->count()->willReturn(1001);

        $massResponse = $this->handle($datagrid, $massAction);

        $translator->trans("oro.grid.mass_action.delete.item_limit", ["%count%" => 1001, "%limit%" => 1000])
            ->willReturn('You cannot mass delete more than 1000 products (1001 selected)');

        $massResponse->shouldHaveType(MassActionResponse::class);
    }
}
