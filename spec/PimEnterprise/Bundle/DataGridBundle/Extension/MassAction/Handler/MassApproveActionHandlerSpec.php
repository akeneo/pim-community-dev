<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Actions\Redirect\EditMassAction;
use Pim\Bundle\FilterBundle\Datasource\FilterProductDatasourceAdapterInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Event\MassActionEvents;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MassApproveActionHandlerSpec extends ObjectBehavior
{
    function let(EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($eventDispatcher);
    }

    function it_handles_edit_mass_action(
        $eventDispatcher,
        DatagridInterface $datagrid,
        DatasourceSpecInterface $datasource,
        EditMassAction $massAction,
        ProductQueryBuilderInterface $pqb,
        ProductDraftInterface $productDraft1,
        ProductDraftInterface $productDraft2,
        ProductDraftInterface $productDraft3
    ) {
        $objectIds = ['foo', 'bar', 'baz'];

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

        $productDraft1->getId()->willReturn('foo');
        $productDraft2->getId()->willReturn('bar');
        $productDraft3->getId()->willReturn('baz');

        $pqb->execute()->willReturn([$productDraft1, $productDraft2, $productDraft3]);

        $this->handle($datagrid, $massAction)->shouldReturn($objectIds);
    }
}

interface DatasourceSpecInterface extends FilterProductDatasourceAdapterInterface
{
}
