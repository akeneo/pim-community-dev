<?php

namespace spec\Pim\Bundle\UserBundle\EventSubscriber\Storage;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\UserBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class AddDefaultGroupToUserSubscriberSpec extends ObjectBehavior
{
    function let(GroupRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_some_events()
    {
        $this->getSubscribedEvents()->shouldReturn([StorageEvents::PRE_SAVE => 'addDefaultGroup']);
    }

    function it_does_nothing_on_non_pim_user(
        $repository,
        GenericEvent $event
    ) {
        $repository->getDefaultUserGroup()->shouldNotBeCalled();
        $event->getSubject()->willReturn('subject');

        $this->addDefaultGroup($event);
    }

    function it_does_nothing_if_user_already_have_a_group(
        $repository,
        GenericEvent $event,
        UserInterface $user,
        Group $redactor
    ) {
        $user->getGroups()->willReturn(new ArrayCollection([$redactor]));
        $event->getSubject()->willReturn($user);
        $repository->getDefaultUserGroup()->shouldNotBeCalled();

        $this->addDefaultGroup($event);
    }

    function it_throws_an_exception_if_default_group_does_not_exists(
        $repository,
        GenericEvent $event,
        UserInterface $user
    ) {
        $user->getGroups()->willReturn(new ArrayCollection());
        $event->getSubject()->willReturn($user);
        $repository->getDefaultUserGroup()->willReturn(null);

        $this->shouldThrow('\RuntimeException')
            ->during('addDefaultGroup', [$event]);
    }

    function it_adds_default_group(
        $repository,
        GenericEvent $event,
        UserInterface $user,
        Group $group
    ) {
        $user->getGroups()->willReturn(new ArrayCollection());
        $user->addGroup($group)->shouldBeCalled();
        $event->getSubject()->willReturn($user);
        $repository->getDefaultUserGroup()->willReturn($group);

        $this->addDefaultGroup($event);
    }
}
