<?php

namespace Specification\Akeneo\UserManagement\Bundle\EventListener;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\ConfigBundle\Entity\ConfigValue;
use Oro\Bundle\ConfigBundle\Entity\Repository\ConfigValueRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Translation\TranslatorInterface;

class LocaleSubscriberSpec extends ObjectBehavior
{
    function let(RequestStack $requestStack, TranslatorInterface $translator, EntityManager $em)
    {
        $this->beConstructedWith($requestStack, $translator, $em);
    }

    function it_implements_an_event_listener_interface()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_sets_locale_on_kernel_request(
        GetResponseEvent $event,
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
        GetResponseEvent $event,
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
        GetResponseEvent $event,
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
        $translator,
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

        $translator->setLocale('fr_FR')->shouldBeCalled();

        $this->onPostUpdate($event);
    }

    function it_does_not_set_locale_on_post_update_when_event_subject_is_different_from_current_user(
        $translator,
        GenericEvent $event,
        UserInterface $user,
        RequestStack $requestStack,
        Request $request,
        LocaleInterface $locale
    ) {
        $event->getSubject()->willReturn($user);
        $event->getArgument('current_user')->willReturn(null);

        $translator->setLocale('fr_FR')->shouldNotBeCalled();

        $this->onPostUpdate($event);
    }
}
