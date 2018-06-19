<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Redirect\EditMassAction;
use Pim\Bundle\FilterBundle\Datasource\FilterProductDatasourceAdapterInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use PimEnterprise\Component\Workflow\Model\ProductDraft;
use PimEnterprise\Component\Workflow\Model\ProductModelDraft;
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
            MassActionEvents::MASS_REFUSE_PRE_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            MassActionEvents::MASS_REFUSE_POST_HANDLER,
            Argument::type('Pim\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvent')
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
