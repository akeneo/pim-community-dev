<?php

namespace Specification\Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector\RequestDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestDataCollectorSpec extends ObjectBehavior
{
    function let(RequestStack $stack)
    {
        $this->beConstructedWith($stack);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RequestDataCollector::class);
        $this->shouldHaveType(DataCollectorInterface::class);
    }

    function it_collects_data_from_request($stack, Request $request)
    {
        $stack->getCurrentRequest()->willReturn($request);
        $request->getHost()->willReturn('http://demo.akeneo.com/');
        $this->collect()->shouldReturn(['pim_host' => 'http://demo.akeneo.com/']);
    }
}
