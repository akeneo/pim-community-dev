<?php

namespace spec\PimEnterprise\Bundle\DataGridBundle\Adapter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Pim\Component\Catalog\Model\FamilyInterface;
use Symfony\Component\HttpFoundation\Request;

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
                'field'    => 'categories.id',
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
                'field'    => 'categories.id',
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

    function it_returns_object_ids_on_approve_grid($massActionDispatcher)
    {
        $massActionDispatcher->dispatch(['gridName' => 'proposal-grid'])->willReturn([1, 2, 5]);
        $massActionDispatcher->getRawFilters(['gridName' => 'proposal-grid'])->shouldNotBeCalled();

        $result = ['values' => [1, 2, 5]];

        $this->adapt(['gridName' => 'proposal-grid'])->shouldReturn($result);
    }
}
