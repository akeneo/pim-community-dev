<?php

namespace spec\Pim\Bundle\UserBundle\EventSubscriber\Storage;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ChangeLocaleOnUserUpdateSubscriberSpec extends ObjectBehavior
{
    function let(UserContext $userContext, RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->beConstructedWith($userContext, $requestStack, $translator);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_some_events()
    {
        $this->getSubscribedEvents()->shouldReturn([StorageEvents::PRE_SAVE => 'setLocale']);
    }

    function it_does_nothing_on_non_pim_user(
        $requestStack,
        $translator,
        GenericEvent $event
    ) {
        $requestStack->getMasterRequest()->shouldNotBeCalled();
        $translator->setLocale(Argument::any())->shouldNotBeCalled();

        $event->getSubject()->willReturn('subject');

        $this->setLocale($event);
    }

    function it_does_nothing_if_the_updated_user_is_not_the_current_user(
        $userContext,
        $requestStack,
        $translator,
        GenericEvent $event,
        UserInterface $mary,
        UserInterface $julia
    ) {
        $requestStack->getMasterRequest()->shouldNotBeCalled();
        $translator->setLocale(Argument::any())->shouldNotBeCalled();

        $event->getSubject()->willReturn($mary);
        $userContext->getUser()->willReturn($julia);

        $this->setLocale($event);
    }

    function it_updates_translator_and_request_with_user_locale(
        $userContext,
        $requestStack,
        $translator,
        Request $request,
        SessionInterface $session,
        GenericEvent $event,
        UserInterface $mary,
        LocaleInterface $locale
    ) {
        $requestStack->getMasterRequest()->willReturn($request);
        $request->getSession()->willReturn($session);
        $mary->getUiLocale()->willReturn($locale);
        $locale->getLanguage()->willReturn('en_US');

        $session->set('_locale', 'en_US')->shouldBeCalled();
        $translator->setLocale('en_US')->shouldBeCalled();

        $event->getSubject()->willReturn($mary);
        $userContext->getUser()->willReturn($mary);

        $this->setLocale($event);
    }
}
