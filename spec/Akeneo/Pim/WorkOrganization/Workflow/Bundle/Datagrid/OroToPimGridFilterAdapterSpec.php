<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid;

use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;
use PhpSpec\ObjectBehavior;

class OroToPimGridFilterAdapterSpec extends ObjectBehavior
{
    function let(MassActionDispatcher $massActionDispatcher)
    {
        $this->beConstructedWith($massActionDispatcher);
    }

    function it_adapts_fiters_for_the_proposal_grid($massActionDispatcher)
    {
        $massActionDispatcher->dispatch(['gridName' => 'proposal-grid','oro grid parameters'])->willReturn(['pim grid parameters']);

        $this->adapt(['gridName' => 'proposal-grid','oro grid parameters'])->shouldReturn(['values' => ['pim grid parameters']]);
    }

    function it_adapts_fiters_for_the_published_product_grid($massActionDispatcher)
    {
        $massActionDispatcher->getRawFilters(['gridName' => 'published-product-grid','oro grid parameters'])->willReturn(['pim grid parameters']);

        $this->adapt(['gridName' => 'published-product-grid','oro grid parameters'])->shouldReturn(['pim grid parameters']);
    }
}
