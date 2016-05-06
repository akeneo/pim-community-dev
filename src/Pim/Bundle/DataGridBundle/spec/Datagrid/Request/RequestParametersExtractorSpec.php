<?php

namespace spec\Pim\Bundle\DataGridBundle\Datagrid\Request;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;

class RequestParametersExtractorSpec extends ObjectBehavior
{
    function let(RequestParameters $requestParams)
    {
        $this->beConstructedWith($requestParams);
    }

    function it_extracts_the_parameter_from_the_datagrid_request(Request $request, $requestParams)
    {
        $this->setRequest($request);
        $requestParams->get('dataLocale', null)->shouldBeCalled()->willReturn('en_US');
        $this->getParameter('dataLocale');
    }

    function it_extracts_the_parameter_from_the_symfony_request(Request $request, $requestParams)
    {
        $this->setRequest($request);
        $requestParams->get('dataLocale', null)->shouldBeCalled();
        $request->get('dataLocale', null)->shouldBeCalled()->willReturn('en_US');
        $this->getParameter('dataLocale');
    }

    function it_trows_a_logic_exception_when_the_parameter_is_not_present(Request $request, $requestParams)
    {
        $this->setRequest($request);
        $requestParams->get('dataLocale', null)->shouldBeCalled();
        $request->get('dataLocale', null)->shouldBeCalled();
        $this->shouldThrow(new \LogicException('Parameter "dataLocale" is expected'))->duringGetParameter('dataLocale');
    }
}
