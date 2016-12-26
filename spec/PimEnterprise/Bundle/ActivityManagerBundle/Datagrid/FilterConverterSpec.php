<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Datagrid;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class FilterConverterSpec extends ObjectBehavior
{
    function let(OroToPimGridFilterAdapter $adapter)
    {
        $this->beConstructedWith($adapter);
    }

    function it_converts_datagrid_filters_into_pqb_filters(
        Request $request,
        ParameterBagInterface $parameterBag
    ) {
        $request->query = $parameterBag;

        $parameterBag->add(
            [
                'gridName' => OroToPimGridFilterAdapter::PRODUCT_GRID_NAME,
                'actionName' => 'mass_edit', //Fake mass action, needed for the grid filter adapter.
                'inset' => false,
                'filters' => 'filters',
            ]
        )->shouldBeCalled();

        $this->convert($request, 'filters');
    }
}
