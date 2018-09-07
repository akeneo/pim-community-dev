<?php

namespace spec\Akeneo\Pim\Permission\Bundle\Datagrid\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ColumnsConfigurator;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\SortersConfigurator;
use Akeneo\Pim\Permission\Bundle\Datagrid\EventListener\ConfigureProductGridListener;
use Akeneo\Pim\Permission\Bundle\Datagrid\Product\ContextConfigurator;
use Akeneo\Pim\Permission\Bundle\Datagrid\Product\RowActionsConfigurator;

class ConfigureProductGridListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ConfigureProductGridListener::class);
    }

    function let(
        ContextConfigurator $contextConfigurator,
        ColumnsConfigurator $columnsConfigurator,
        FiltersConfigurator $filtersConfigurator,
        SortersConfigurator $sortersConfigurator,
        RowActionsConfigurator $rowActionsConfigurator
    ) {
        $this->beConstructedWith(
            $contextConfigurator,
            $columnsConfigurator,
            $filtersConfigurator,
            $sortersConfigurator,
            $rowActionsConfigurator
        );
    }

    function it_builds_the_datagrid(BuildBefore $event, DatagridConfiguration $dataGridConfiguration)
    {
        $event->getConfig()->willReturn($dataGridConfiguration);

        $this->buildBefore($event);
    }
}
