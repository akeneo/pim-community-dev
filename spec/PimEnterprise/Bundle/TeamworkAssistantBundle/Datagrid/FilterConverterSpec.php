<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class FilterConverterSpec extends ObjectBehavior
{
    function let(OroToPimGridFilterAdapter $adapter, MassActionParametersParser $parameterParser)
    {
        $this->beConstructedWith($adapter, $parameterParser);
    }

    function it_converts_datagrid_filters_into_pqb_filters(
        Request $request,
        ParameterBagInterface $parameterBag,
        $parameterParser
    ) {
        $request->query = $parameterBag;
        $parameters = $parameterParser->parse($request)->willReturn([]);

        $parameterBag->add(
            [
                'gridName' => OroToPimGridFilterAdapter::PRODUCT_GRID_NAME,
                'actionName' => 'product_edit', //Fake mass action, needed for the grid filter adapter.
                'inset' => false,
                'filters' => 'filters',
            ]
        )->shouldBeCalled();

        $this->convert($request, 'filters');
    }
}
