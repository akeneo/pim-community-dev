<?php

namespace spec\Pim\Bundle\VersioningBundle\EventSubscriber;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class AddUserSubscriberSpec extends ObjectBehavior
{
    function let(VersionManager $versionManager, SecurityContextInterface $security, TokenInterface $token)
    {
        $this->beConstructedWith($versionManager, $security);

        $security->isGranted(Argument::any())->willReturn(true);
    }

    function it_is_an_event_listener()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_the_kernel_request_event()
    {
        $this->getSubscribedEvents()->shouldReturn([KernelEvents::REQUEST => 'onKernelRequest']);
    }

    function it_injects_current_username_into_the_version_manager(
        GetResponseEvent $event,
        $security,
        $token,
        User $user,
        $versionManager
    ) {
        $security->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('foo');

        $versionManager->setUsername('foo')->shouldBeCalled();

        $this->onKernelRequest($event);
    }

    function it_does_nothing_if_a_token_is_not_present_in_the_security_context(
        GetResponseEvent $event,
        $security,
        $versionManager
    ) {
        $security->getToken()->willReturn(null);

        $versionManager->setUsername(Argument::any())->shouldNotBeCalled();

        $this->onKernelRequest($event);
    }
}
