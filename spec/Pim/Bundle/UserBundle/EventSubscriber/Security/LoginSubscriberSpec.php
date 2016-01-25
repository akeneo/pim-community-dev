<?php

namespace spec\Pim\Bundle\UserBundle\EventSubscriber\Security;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginSubscriberSpec extends ObjectBehavior
{
    function let(SaverInterface $saver)
    {
        $this->beConstructedWith($saver);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_some_events()
    {
        $this->getSubscribedEvents()->shouldReturn(['security.interactive_login' => 'onLogin']);
    }

    function it_does_nothing_on_non_authenticated_user(
        $saver,
        InteractiveLoginEvent $event,
        TokenInterface $token
    ) {
        $event->getAuthenticationToken()->willReturn($token);
        $token->getUser()->willReturn('anon.');
        $saver->save(Argument::any())->shouldNotBeCalled();

        $this->onLogin($event);
    }

    function it_does_nothing_on_non_pim_user(
        $saver,
        InteractiveLoginEvent $event,
        TokenInterface $token,
        SymfonyUserInterface $user
    ) {
        $event->getAuthenticationToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $saver->save(Argument::any())->shouldNotBeCalled();

        $this->onLogin($event);
    }

    function it_updates_the_user_info(
        $saver,
        InteractiveLoginEvent $event,
        TokenInterface $token,
        UserInterface $user
    ) {
        $event->getAuthenticationToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getLoginCount()->willReturn(2);

        $user->setLastLogin(Argument::type('\DateTime'))->shouldBeCalled();
        $user->setLoginCount(3)->shouldBeCalled();
        $saver->save($user)->shouldBeCalled();

        $this->onLogin($event);
    }
}
