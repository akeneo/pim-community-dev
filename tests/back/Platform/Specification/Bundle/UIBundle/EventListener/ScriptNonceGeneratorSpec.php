<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ScriptNonceGeneratorSpec extends ObjectBehavior
{
    function let(RequestStack $requestStack, Request $request)
    {
        $requestStack->getCurrentRequest()->willReturn($request);
        $this->beConstructedWith($requestStack, 'my_secret');
    }

    function it_gets_the_nonce_from_bapid_cookie(Request $request, ParameterBag $cookies)
    {
        $cookies->get('BAPID')->willReturn('my_bap_id');
        $request->cookies = $cookies;

        $this->getGeneratedNonce()->shouldReturn('94d18804ef7db19a4654c6be2e9a581fb1551dbf');
    }
}
