<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Adapter;

use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

class OroToPimGridFilterAdapterSpec extends ObjectBehavior
{
    function let(MassActionDispatcher $massActionDispatcher)
    {
        $this->beConstructedWith($massActionDispatcher);
    }

    function it_returns_raw_filters($massActionDispatcher)
    {
        $massActionDispatcher->getRawFilters(['gridName' => 'product-grid'])->willReturn([
            [
                'field'    => 'sku',
                'operator' => 'CONTAINS',
                'value'    => 'DP',
            ],
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => [12, 13, 14],
            ]
        ]);

        $this->adapt(['gridName' => 'product-grid'])->shouldReturn([
            [
                'field'    => 'sku',
                'operator' => 'CONTAINS',
                'value'    => 'DP',
            ],
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => [12, 13, 14],
            ]
        ]);
    }

    function it_returns_filters_on_family_grid(
        $massActionDispatcher,
        FamilyInterface $family1,
        FamilyInterface $family2
    ) {
        $massActionDispatcher->dispatch(['gridName' => 'family-grid'])->willReturn([$family1, $family2]);
        $family1->getId()->willReturn(45);
        $family2->getId()->willReturn(70);

        $massActionDispatcher->getRawFilters(['gridName' => 'family-grid'])->shouldNotBeCalled();

        $this->adapt(['gridName' => 'family-grid'])->shouldReturn([[
            'field'    => 'id',
            'operator' => 'IN',
            'value'    => [45, 70],
        ]]);
    }
}
