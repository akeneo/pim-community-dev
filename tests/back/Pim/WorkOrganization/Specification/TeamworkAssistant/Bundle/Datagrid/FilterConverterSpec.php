<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

class FilterConverterSpec extends ObjectBehavior
{
    function let(OroToPimGridFilterAdapter $adapter, MassActionParametersParser $parameterParser)
    {
        $this->beConstructedWith($adapter, $parameterParser);
    }

    function it_converts_datagrid_filters_into_pqb_filters(
        $adapter,
        $parameterParser,
        Request $request,
        ParameterBagInterface $parameterBag
    ) {
        $request->query = $parameterBag;

        $parameterBag->add(
            [
                'gridName' => OroToPimGridFilterAdapter::PRODUCT_GRID_NAME,
                'inset'    => false,
                'filters'  => 'filters',
            ]
        )->shouldBeCalled();

        $parameterParser->parse($request)->willReturn(['parsed_params']);
        $adapter->adapt(['parsed_params'])->shouldBeCalled();

        $this->convert($request, 'filters');
    }
}
