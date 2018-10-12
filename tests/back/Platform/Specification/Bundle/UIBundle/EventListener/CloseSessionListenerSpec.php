<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CloseSessionListenerSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf(EventSubscriberInterface::class);
    }

    function it_subscribes_to_kernel_request()
    {
        $this->getSubscribedEvents()->shouldReturn([KernelEvents::REQUEST => ['closeSession', -100]]);
    }

    function it_closes_an_opened_session(GetResponseEvent $event, Request $request, SessionInterface $session)
    {
        $event->getRequest()->willReturn($request);
        $request->hasSession()->willReturn(true);
        $request->getSession()->willReturn($session);
        $session->isStarted()->willReturn(true);
        $session->save()->shouldBeCalled();

        $this->closeSession($event);
    }

    function it_does_not_close_session_if_no_session_in_the_request(
        GetResponseEvent $event,
        Request $request
    ) {
        $event->getRequest()->willReturn($request);
        $request->hasSession()->willReturn(false);
        $request->getSession()->shouldNotBeCalled();

        $this->closeSession($event);
    }

    function it_does_not_close_session_if_session_is_not_started(
        GetResponseEvent $event,
        Request $request,
        SessionInterface $session
    ) {
        $event->getRequest()->willReturn($request);
        $request->hasSession()->willReturn(true);
        $request->getSession()->willReturn($session);
        $session->isStarted()->willReturn(false);
        $session->save()->shouldNotBeCalled();

        $this->closeSession($event);
    }
}
