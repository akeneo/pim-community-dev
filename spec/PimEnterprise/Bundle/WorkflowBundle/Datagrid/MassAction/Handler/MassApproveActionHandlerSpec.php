<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Datagrid\MassAction\Handler;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Redirect\EditMassAction;
use Pim\Bundle\FilterBundle\Datasource\FilterProductDatasourceAdapterInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MassApproveActionHandlerSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $eventDispatcher, CursorFactoryInterface $cursorFactory)
    {
        $this->beConstructedWith($eventDispatcher, $cursorFactory);
    }

    function it_handles_edit_mass_action(
        $eventDispatcher,
        $cursorFactory,
        SearchQueryBuilder $searchQueryBuilder,
        DatagridInterface $datagrid,
        FilterProductDatasourceAdapterInterface $datasource,
        EditMassAction $massAction,
        ProductQueryBuilderInterface $pqb,
        EntityWithValuesDraftInterface $productDraft1,
        EntityWithValuesDraftInterface $productDraft2,
        EntityWithValuesDraftInterface $productDraft3
    ) {
        $objectIds = [1, 2, 3];

        $eventDispatcher->dispatch(
            MassActionEvents::MASS_APPROVE_PRE_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            MassActionEvents::MASS_APPROVE_POST_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();

        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->getProductQueryBuilder()->willReturn($pqb);

        $pqb->getQueryBuilder()->willReturn($searchQueryBuilder);
        $searchQueryBuilder->getQuery()->willReturn([]);

        $productDraft1->getId()->willReturn(1);
        $productDraft2->getId()->willReturn(2);
        $productDraft3->getId()->willReturn(3);

        $cursorFactory->createCursor([])->willReturn([$productDraft1, $productDraft2, $productDraft3]);

        $this->handle($datagrid, $massAction)->shouldReturn($objectIds);
    }
}
