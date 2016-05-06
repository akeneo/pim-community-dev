<?php

namespace spec\Pim\Bundle\AnalyticsBundle\Provider;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ServerBag;

class ServerVersionProviderSpec extends ObjectBehavior
{
    function let(RequestStack $requestStack)
    {
        $this->beConstructedWith($requestStack);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\AnalyticsBundle\Provider\ServerVersionProvider');
    }

    function it_provides_server_version_of_pim_host($requestStack, Request $request, ServerBag $serverBag)
    {
        $requestStack->getCurrentRequest()->willReturn($request);

        $request->server = $serverBag;
        $serverBag->get('SERVER_SOFTWARE')->willReturn('Apache/2.4.18 (Debian)');

        $this->provide()->shouldReturn(['server_version' => 'Apache/2.4.18 (Debian)']);
    }

    function it_does_not_provides_server_version_of_pim_host_if_request_is_null($requestStack, ServerBag $serverBag)
    {
        $requestStack->getCurrentRequest()->willReturn(null);

        $serverBag->get(Argument::type('string'))->shouldNotBeCalled();

        $this->provide()->shouldReturn(['server_version' => '']);
    }
}
