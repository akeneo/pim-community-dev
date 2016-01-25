<?php

namespace spec\Pim\Bundle\UserBundle\EventSubscriber\Security;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class ChangeLocaleOnLoginSubscriberSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_some_events()
    {
        $this->getSubscribedEvents()->shouldReturn([SecurityEvents::INTERACTIVE_LOGIN => 'setLocale']);
    }

    function it_sets_session_locale_based_on_user(
        InteractiveLoginEvent $event,
        TokenInterface $token,
        UserInterface $user,
        LocaleInterface $locale,
        Request $request,
        SessionInterface $session
    ) {
        $event->getRequest()->willReturn($request);
        $event->getAuthenticationToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUiLocale()->willReturn($locale);
        $locale->getLanguage()->willReturn('en_US');
        $request->getSession()->willReturn($session);
        $session->set('_locale', 'en_US')->shouldBeCalled();

        $this->setLocale($event);
    }

    function it_does_nothing_on_non_authenticated_user(
        InteractiveLoginEvent $event,
        TokenInterface $token
    ) {
        $event->getAuthenticationToken()->willReturn($token);
        $token->getUser()->willReturn('anon.');
        $event->getRequest()->shouldNotBeCalled();

        $this->setLocale($event);
    }

    function it_does_nothing_on_non_pim_user(
        InteractiveLoginEvent $event,
        TokenInterface $token,
        SymfonyUserInterface $user
    ) {
        $event->getAuthenticationToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $event->getRequest()->shouldNotBeCalled();

        $this->setLocale($event);
    }
}
