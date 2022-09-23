<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\MassAction\Handler;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Datasource\ProductProposalDatasource;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\MassActionEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Redirect\EditMassAction;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MassRefuseActionHandlerSpec extends ObjectBehavior
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
        ProductProposalDatasource $datasource,
        EditMassAction $massAction,
        ProductQueryBuilderInterface $pqb,
        ProductDraft $productDraft1,
        ProductDraft $productDraft2,
        ProductDraft $productDraft3,
        ProductModelDraft $productModelDraft,
        CursorInterface $cursor
    ) {
        $objectIds = [
            'product_draft_ids'       => [1, 2, 3],
            'product_model_draft_ids' => [1],
        ];

        $eventDispatcher->dispatch(
            Argument::type(MassActionEvent::class),
            MassActionEvents::MASS_REFUSE_PRE_HANDLER
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            Argument::type(MassActionEvent::class),
            MassActionEvents::MASS_REFUSE_POST_HANDLER
        )->shouldBeCalled();

        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->getProductQueryBuilder()->willReturn($pqb);

        $pqb->getQueryBuilder()->willReturn($searchQueryBuilder);
        $searchQueryBuilder->getQuery()->willReturn([]);

        $productDraft1->getId()->willReturn(1);
        $productDraft2->getId()->willReturn(2);
        $productDraft3->getId()->willReturn(3);
        $productModelDraft->getId()->willReturn(1);

        $cursor->rewind()->shouldBeCalledOnce();
        $cursor->valid()->shouldBeCalledTimes(5)->willReturn(true, true, true, true, false);
        $cursor->current()->shouldBeCalledTimes(4)->willReturn($productDraft1, $productDraft2, $productDraft3, $productModelDraft);
        $cursor->next()->shouldBeCalledTimes(4);
        $cursorFactory->createCursor([])->willReturn($cursor);

        $this->handle($datagrid, $massAction)->shouldReturn($objectIds);
    }
}
