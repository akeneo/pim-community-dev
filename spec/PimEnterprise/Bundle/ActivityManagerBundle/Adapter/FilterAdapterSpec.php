<?php

namespace spec\Akeneo\ActivityManager\Bundle\Adapter;

use Akeneo\ActivityManager\Component\Adapter\FilterAdapterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class FilterAdapterSpec extends ObjectBehavior
{
    function let(OroToPimGridFilterAdapter $adapter)
    {
        $this->beConstructedWith($adapter);
    }

    function it_is_a_filter_adapter()
    {
        $this->shouldImplement(FilterAdapterInterface::class);
    }

    function it_adapts(
        Request $request,
        ParameterBagInterface $parameterBag
    ) {
        $request->query = $parameterBag;

        $parameterBag->add(
            [
                'gridName'   => OroToPimGridFilterAdapter::PRODUCT_GRID_NAME,
                'actionName' => 'mass_edit', //Fake mass action, needed for the grid filter adapter.
                'inset'      => false,
                'filters'    => 'filters',
            ]
        )->shouldBeCalled();

        $this->adapt($request, 'filters');
    }
}
