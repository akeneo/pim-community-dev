<?php

namespace spec\Akeneo\Pim\Automation\RuleEngine\Bundle\Datagrid;

use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;
use PhpSpec\ObjectBehavior;

class OroToPimGridFilterAdapterSpec extends ObjectBehavior
{
    function let(MassActionDispatcher $massActionDispatcher)
    {
        $this->beConstructedWith($massActionDispatcher);
    }

    function it_adapts_fiters_for_the_rule_grid($massActionDispatcher)
    {
        $massActionDispatcher->dispatch(['oro grid parameters'])->willReturn(['pim grid parameters']);

        $this->adapt(['oro grid parameters'])->shouldReturn(['values' => ['pim grid parameters']]);
    }
}
