<?php

namespace spec\Akeneo\Asset\Bundle\Event;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Permission\Bundle\Datagrid\Product\ContextConfigurator;

class ConfigureAssetGridListenerSpec extends ObjectBehavior
{
    function let(ContextConfigurator $contextConfigurator)
    {
        $this->beConstructedWith($contextConfigurator);
    }

    function it_builds_the_datagrid(
        $contextConfigurator,
        BuildBefore $event,
        DatagridConfiguration $dataGridConfiguration
    ) {
        $event->getConfig()->willReturn($dataGridConfiguration);
        $contextConfigurator->configure($dataGridConfiguration);

        $this->buildBefore($event);
    }
}
