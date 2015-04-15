<?php

namespace spec\Pim\Bundle\DataGridBundle\Adapter;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;

class OroToPimGridFilterAdapterSpec extends ObjectBehavior
{
    function let(MassActionDispatcher $massActionDispatcher)
    {
        $this->beConstructedWith($massActionDispatcher);
    }

    function it_returns_applied_filters(Request $request, $massActionDispatcher)
    {
        $massActionDispatcher->getAppliedFilters($request)->willReturn([[
            'field'    => 'id',
            'operator' => 'IN',
            'value'    => 1,
        ]]);

        $this->adapt($request)->shouldReturn([[
            'field'    => 'id',
            'operator' => 'IN',
            'value'    => 1,
        ]]);
    }
}
