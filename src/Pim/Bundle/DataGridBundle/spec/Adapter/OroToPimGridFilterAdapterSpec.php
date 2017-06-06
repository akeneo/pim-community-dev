<?php

namespace spec\Pim\Bundle\DataGridBundle\Adapter;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Symfony\Component\HttpFoundation\Request;

class OroToPimGridFilterAdapterSpec extends ObjectBehavior
{
    function let(MassActionDispatcher $massActionDispatcher)
    {
        $this->beConstructedWith($massActionDispatcher);
    }

    function it_returns_raw_filters($massActionDispatcher, Request $request)
    {
        $request->get('gridName')->willReturn('product-grid');

        $massActionDispatcher->getRawFilters($request)->willReturn([
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

        $this->adapt($request)->shouldReturn([
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
        Request $request,
        FamilyInterface $family1,
        FamilyInterface $family2
    ) {
        $request->get('gridName')->willReturn('family-grid');

        $massActionDispatcher->dispatch($request)->willReturn([$family1, $family2]);
        $family1->getId()->willReturn(45);
        $family2->getId()->willReturn(70);

        $massActionDispatcher->getRawFilters($request)->shouldNotBeCalled();

        $this->adapt($request)->shouldReturn([[
            'field'    => 'id',
            'operator' => 'IN',
            'value'    => [45, 70],
        ]]);
    }
}
