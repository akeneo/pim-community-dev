<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\MassAction\Handler;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvent;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Redirect\EditMassAction;
use Oro\Bundle\PimFilterBundle\Datasource\FilterProductDatasourceAdapterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\MassActionEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
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
        DatasourceSpecInterface $datasource,
        EditMassAction $massAction,
        ProductQueryBuilderInterface $pqb,
        ProductDraft $productDraft1,
        ProductDraft $productDraft2,
        ProductDraft $productDraft3,
        ProductModelDraft $productModelDraft
    ) {
        $objectIds = [
            'product_draft_ids'       => [1, 2, 3],
            'product_model_draft_ids' => [1],
        ];

        $eventDispatcher->dispatch(
            MassActionEvents::MASS_APPROVE_PRE_HANDLER,
            Argument::type(MassActionEvent::class)
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            MassActionEvents::MASS_APPROVE_POST_HANDLER,
            Argument::type(MassActionEvent::class)
        )->shouldBeCalled();

        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->getProductQueryBuilder()->willReturn($pqb);

        $pqb->getQueryBuilder()->willReturn($searchQueryBuilder);
        $searchQueryBuilder->getQuery()->willReturn([]);

        $productDraft1->getId()->willReturn(1);
        $productDraft2->getId()->willReturn(2);
        $productDraft3->getId()->willReturn(3);
        $productModelDraft->getId()->willReturn(1);

        $cursorFactory->createCursor([])->willReturn([$productDraft1, $productDraft2, $productDraft3, $productModelDraft]);

        $this->handle($datagrid, $massAction)->shouldReturn($objectIds);
    }
}

interface DatasourceSpecInterface extends FilterProductDatasourceAdapterInterface
{
}
