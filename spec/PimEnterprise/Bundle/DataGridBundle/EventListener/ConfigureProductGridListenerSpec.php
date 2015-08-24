<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\ColumnsConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use Pim\Bundle\DataGridBundle\Datagrid\Configuration\Product\SortersConfigurator;
use PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use PimEnterprise\Bundle\DataGridBundle\Datagrid\Configuration\Product\RowActionsConfigurator;

class ConfigureProductGridListenerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\DataGridBundle\EventListener\ConfigureProductGridListener');
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
