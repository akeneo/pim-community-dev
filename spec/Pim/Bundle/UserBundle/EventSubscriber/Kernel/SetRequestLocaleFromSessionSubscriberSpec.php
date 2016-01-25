<?php

namespace spec\Pim\Bundle\UserBundle\EventSubscriber\Kernel;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SetRequestLocaleFromSessionSubscriberSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_some_events()
    {
        $this->getSubscribedEvents()->shouldReturn([KernelEvents::REQUEST  => [['onKernelRequest', 17]]]);
    }

    function it_sets_request_locale_based_on_session(GetResponseEvent $event, Request $request, SessionInterface $session)
    {
        $event->getRequest()->willReturn($request);
        $request->getSession()->willReturn($session);
        $session->get('_locale')->willReturn('en_US');
        $request->setLocale('en_US')->shouldBeCalled();;

        $this->onKernelRequest($event);
    }
}
