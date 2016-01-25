<?php

namespace spec\Pim\Bundle\UserBundle\EventSubscriber\Storage;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class SetPasswordFromPlainPasswordSubscriberSpec extends ObjectBehavior
{
    function let(EncoderFactoryInterface $encoderFactory)
    {
        $this->beConstructedWith($encoderFactory);
    }

    function it_subscribes_to_some_events()
    {
        $this->getSubscribedEvents()->shouldReturn([StorageEvents::PRE_SAVE => 'setPassword']);
    }

    function it_does_nothing_on_wrong_subject($encoderFactory, GenericEvent $event)
    {
        $event->getSubject()->willReturn('subject');
        $encoderFactory->getEncoder(Argument::any())->shouldNotBeCalled();

        $this->setPassword($event);
    }

    function it_does_nothing_if_plain_password_is_empty($encoderFactory, GenericEvent $event, UserInterface $user)
    {
        $event->getSubject()->willReturn($user);
        $user->getPlainPassword()->willReturn(null);
        $encoderFactory->getEncoder(Argument::any())->shouldNotBeCalled();

        $this->setPassword($event);
    }

    function it_set_new_password(
        $encoderFactory,
        GenericEvent $event,
        UserInterface $user,
        PasswordEncoderInterface $encoder
    ) {
        $event->getSubject()->willReturn($user);
        $user->getPlainPassword()->willReturn('foobar');
        $user->getSalt()->willReturn('salt');
        $encoderFactory->getEncoder($user)->willReturn($encoder);
        $encoder->encodePassword('foobar', 'salt')->willReturn('pepper');

        $user->setPassword('pepper')->shouldBeCalled();
        $user->eraseCredentials()->shouldBeCalled();

        $this->setPassword($event);
    }
}
