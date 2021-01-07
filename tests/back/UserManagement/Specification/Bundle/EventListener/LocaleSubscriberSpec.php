<?php

namespace Specification\Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\LocaleAwareInterface;

class LocaleSubscriberSpec extends ObjectBehavior
{
    function let(RequestStack $requestStack, LocaleAwareInterface $localeAware, EntityManager $em)
    {
        $this->beConstructedWith($requestStack, $localeAware, $em);
        $this->beConstructedWith($requestStack, $localeAware, $em);
    }

    function it_implements_an_event_listener_interface()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_sets_locale_on_kernel_request(
        RequestEvent $event,
        Request $request,
        SessionInterface $session
    ) {
        $event->getRequest()->willReturn($request);
        $request->getSession()->willReturn($session);
        $session->get('_locale')->willReturn('fr_FR');
        $request->setLocale('fr_FR')->shouldBeCalled();

        $this->onKernelRequest($event);
    }

    function it_not_sets_locale_on_kernel_request_if_user_session_is_null_and_config_does_not_exists(
        $em,
        RequestEvent $event,
        Request $request,
        SessionInterface $session,
        Connection $connection,
        Statement $statement
    ) {
        $event->getRequest()->willReturn($request);
        $request->getSession()->willReturn($session);
        $session->get('_locale')->willReturn(null);

        $em->getConnection()->willReturn($connection);
        $connection->executeQuery('SELECT value FROM oro_config_value WHERE name = "language" AND section = "pim_ui" LIMIT 1')->willReturn($statement);

        $statement->fetchColumn(Argument::any())->willReturn(false);

        $request->setLocale()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_sets_locale_from_config_on_kernel_request_if_user_session_is_null(
        $em,
        RequestEvent $event,
        Request $request,
        SessionInterface $session,
        Connection $connection,
        Statement $statement
    ) {
        $event->getRequest()->willReturn($request);
        $request->getSession()->willReturn($session);
        $session->get('_locale')->willReturn(null);

        $em->getConnection()->willReturn($connection);
        $connection->executeQuery('SELECT value FROM oro_config_value WHERE name = "language" AND section = "pim_ui" LIMIT 1')->willReturn($statement);

        $statement->fetchColumn(Argument::any())->willReturn('fr_FR');

        $request->setLocale('fr_FR')->shouldBeCalled();

        $this->onKernelRequest($event);
    }

    function it_sets_locale_on_post_update_when_user_locale_is_set(
        LocaleAwareInterface $localeAware,
        GenericEvent $event,
        UserInterface $user,
        RequestStack $requestStack,
        Request $request,
        SessionInterface $session,
        LocaleInterface $locale
    ) {
        $event->getSubject()->willReturn($user);
        $event->getArgument('current_user')->willReturn($user);

        $requestStack->getMasterRequest()->willReturn($request);
        $request->getSession()->willReturn($session);

        $user->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('fr_FR');

        $localeAware->setLocale('fr_FR')->shouldBeCalled();

        $this->onPostUpdate($event);
    }

    function it_does_not_set_locale_on_post_update_when_event_subject_is_different_from_current_user(
        LocaleAwareInterface $localeAware,
        GenericEvent $event,
        UserInterface $user
    ) {
        $event->getSubject()->willReturn($user);
        $event->getArgument('current_user')->willReturn(null);

        $localeAware->setLocale('fr_FR')->shouldNotBeCalled();

        $this->onPostUpdate($event);
    }
}
