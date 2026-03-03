<?php

namespace Specification\Akeneo\UserManagement\Bundle\EventListener;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\LocaleAwareInterface;

class LocaleSubscriberSpec extends ObjectBehavior
{
    function let(
        RequestStack $requestStack,
        LocaleAwareInterface $localeAware,
        EntityManager $em,
        FirewallMap $firewall
    ) {
        $firewallConfig = new FirewallConfig('foo', 'foo', null, true, false);
        $firewall->getFirewallConfig(Argument::any())->willReturn($firewallConfig);

        $this->beConstructedWith($requestStack, $localeAware, $em, $firewall);
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
        $request->hasSession()->willReturn(true);
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
        Result $result
    ) {
        $event->getRequest()->willReturn($request);
        $request->hasSession()->willReturn(true);
        $request->getSession()->willReturn($session);
        $session->get('_locale')->willReturn(null);

        $em->getConnection()->willReturn($connection);
        $connection->executeQuery('SELECT value FROM oro_config_value WHERE name = "language" AND section = "pim_ui" LIMIT 1')->willReturn($result);

        $result->fetchOne()->willReturn(false);

        $request->setLocale()->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }

    function it_sets_locale_from_config_on_kernel_request_if_user_session_is_null(
        $em,
        RequestEvent $event,
        Request $request,
        SessionInterface $session,
        Connection $connection,
        Result $result
    ) {
        $event->getRequest()->willReturn($request);
        $request->hasSession()->willReturn(true);
        $request->getSession()->willReturn($session);
        $session->get('_locale')->willReturn(null);

        $em->getConnection()->willReturn($connection);
        $connection->executeQuery('SELECT value FROM oro_config_value WHERE name = "language" AND section = "pim_ui" LIMIT 1')->willReturn($result);

        $result->fetchOne()->willReturn('fr_FR');

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

        $requestStack->getMainRequest()->willReturn($request);
        $request->hasSession()->willReturn(true);
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
    
    function it_sets_locale_to_en_us_on_kernel_request_when_the_firewall_name_is_oauth_token(
        RequestEvent $event,
        Request $request,
        FirewallMap $firewall,
    ) {
        $event->getRequest()->willReturn($request);
        $firewallConfig = new FirewallConfig('oauth_token', 'foo', null, true, false);
        $firewall->getFirewallConfig(Argument::any())->willReturn($firewallConfig);

        $request->setLocale('en_US')->shouldBeCalled();

        $this->onKernelRequest($event);
    }
}
