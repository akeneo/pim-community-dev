<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvent;
use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvents;
use Akeneo\UserManagement\Component\Model\User;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AddUserSubscriberSpec extends ObjectBehavior
{
    function let(
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith($authorizationChecker, $tokenStorage);

        $authorizationChecker->isGranted(Argument::any())->willReturn(true);
    }

    function it_is_an_event_listener()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_the_kernel_request_event()
    {
        $this->getSubscribedEvents()->shouldReturn([BuildVersionEvents::PRE_BUILD => 'preBuild']);
    }

    function it_injects_current_username_into_the_version_manager(
        BuildVersionEvent $event,
        $tokenStorage,
        $token,
        User $user
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('foo');

        $this->preBuild($event);
    }

    function it_does_nothing_if_a_token_is_not_present_in_the_security_context(BuildVersionEvent $event, $tokenStorage)
    {
        $tokenStorage->getToken()->willReturn(null);

        $this->preBuild($event);
    }
}
