<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Resolver;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LocaleResolverSpec extends ObjectBehavior
{
    function let(RequestStack $requestStack)
    {
        $this->beConstructedWith($requestStack, 'en');
    }

    function it_returns_locale($requestStack, Request $request)
    {
        $request->getLocale()->willReturn('en_US');
        $requestStack->getCurrentRequest()->willReturn($request);

        $this->getCurrentLocale()->shouldReturn('en_US');
    }

    function it_returns_default_locale_when_request_is_null($requestStack)
    {
        $requestStack->getCurrentRequest()->willReturn(null);

        $this->getCurrentLocale()->shouldReturn('en');
    }
}
