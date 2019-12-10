<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ScriptNonceGeneratorSpec extends ObjectBehavior
{
    function let(RequestStack $requestStack, Request $request)
    {
        $requestStack->getCurrentRequest()->willReturn($request);
        $this->beConstructedWith($requestStack);
    }

    function it_generate_a_new_nonce_on_first_request(Request $request, SessionInterface $session)
    {
        $request->getSession()->willReturn($session);
        $session->get('nonce', null)->willReturn(null);
        $session->set('nonce', Argument::type('string'))->shouldBeCalledTimes(1);

        $this->getGeneratedNonce()->shouldMatch('/\w{8}-\w{4}-\w{4}-\w{4}-\w{8}/');
    }

    function it_get_the_nonce_from_session(Request $request, SessionInterface $session)
    {
        $request->getSession()->willReturn($session);
        $session->set('nonce', null)->shouldNotBeCalled();
        $session->get('nonce', null)->willReturn('session_nonce');
        $session->set('nonce', Argument::type('string'))->shouldNotBeCalled();

        $this->getGeneratedNonce()->shouldReturn('session_nonce');
    }
}
