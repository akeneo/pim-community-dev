<?php

namespace Specification\Akeneo\Asset\Bundle\Event;

use Akeneo\Asset\Bundle\Datagrid\Configuration\RowActionsConfigurator;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Permission\Bundle\Datagrid\Product\ContextConfigurator;

class ConfigureAssetGridListenerSpec extends ObjectBehavior
{
    function let(RowActionsConfigurator $rowConfigurator, ContextConfigurator $contextConfigurator)
    {
        $this->beConstructedWith($rowConfigurator, $contextConfigurator);
    }

    function it_builds_the_datagrid(
        $contextConfigurator,
        $rowConfigurator,
        BuildBefore $event,
        DatagridConfiguration $dataGridConfiguration
    ) {
        $event->getConfig()->willReturn($dataGridConfiguration);

        $contextConfigurator->configure($dataGridConfiguration);
        $rowConfigurator->configure($dataGridConfiguration);

        $this->buildBefore($event);
    }
}
