<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\Extension\MassAction;

use Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid\MassActionEvents;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event\MassActionEvent;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Redirect\EditMassAction;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RuleImpactedProductCountActionHandlerSpec extends ObjectBehavior
{
    function let(HydratorInterface $hydrator, EventDispatcherInterface $eventDispatcher)
    {
        $this->beConstructedWith($hydrator, $eventDispatcher);
    }

    function it_handles_edit_mass_action(
        $eventDispatcher,
        $hydrator,
        DatagridInterface $datagrid,
        DatasourceInterface $datasource,
        EditMassAction $massAction
    ) {
        $objectIds = ['foo', 'bar', 'baz'];

        $eventDispatcher->dispatch(
            Argument::type(MassActionEvent::class),
            MassActionEvents::MASS_RULE_IMPACTED_PRODUCT_COUNT_PRE_HANDLER
        )->shouldBeCalled();
        $eventDispatcher->dispatch(
            Argument::type(MassActionEvent::class),
            MassActionEvents::MASS_RULE_IMPACTED_PRODUCT_COUNT_POST_HANDLER
        )->shouldBeCalled();

        $datagrid->getDatasource()->willReturn($datasource);
        $datasource->setHydrator($hydrator)->shouldBeCalled();
        $datasource->getResults()->willReturn($objectIds);

        $this->handle($datagrid, $massAction)->shouldReturn($objectIds);
    }
}
