<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Datagrid\Request;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestParametersExtractorSpec extends ObjectBehavior
{
    function let(RequestParameters $requestParams, RequestStack $requestStack)
    {
        $this->beConstructedWith($requestParams, $requestStack);
    }

    function it_extracts_the_parameter_from_the_datagrid_request(Request $request, $requestParams, $requestStack)
    {
        $requestStack->getCurrentRequest()->willReturn($request);
        $requestParams->get('dataLocale', null)->shouldBeCalled()->willReturn('en_US');
        $this->getParameter('dataLocale');
    }

    function it_extracts_the_parameter_from_the_symfony_request(Request $request, $requestParams, $requestStack)
    {
        $requestStack->getCurrentRequest()->willReturn($request);
        $requestParams->get('dataLocale', null)->shouldBeCalled();
        $request->get('dataLocale', null)->shouldBeCalled()->willReturn('en_US');
        $this->getParameter('dataLocale');
    }

    function it_trows_a_logic_exception_when_the_parameter_is_not_present(Request $request, $requestParams, $requestStack)
    {
        $requestStack->getCurrentRequest()->willReturn($request);
        $requestParams->get('dataLocale', null)->shouldBeCalled();
        $request->get('dataLocale', null)->shouldBeCalled();
        $this->shouldThrow(new \LogicException('Parameter "dataLocale" is expected'))->duringGetParameter('dataLocale');
    }
}
