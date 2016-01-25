<?php

namespace spec\Pim\Bundle\UserBundle\EventSubscriber\Storage;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\UserBundle\Entity\User;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class GroupSubscriberSpec extends ObjectBehavior
{
    function it_subscribes_to_some_events()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_REMOVE => 'preDeleteGroup',
            StorageEvents::PRE_SAVE   => 'preUpdateGroup',
        ]);
    }

    function it_does_nothing_on_pre_delete_with_wrong_subject(GenericEvent $event)
    {
        $event->getSubject()->willReturn('subject');

        $this->preDeleteGroup($event);
    }

    function it_does_nothing_on_pre_update_with_wrong_subject(GenericEvent $event)
    {
        $event->getSubject()->willReturn('subject');

        $this->preUpdateGroup($event);
    }

    function it_does_nothing_on_pre_delete_with_non_default_group(GenericEvent $event, Group $group)
    {
        $group->getName()->willReturn('foo');
        $event->getSubject()->willReturn($group);

        $this->preDeleteGroup($event);
    }

    function it_does_nothing_on_pre_update_with_non_default_group(GenericEvent $event, Group $group)
    {
        $group->getName()->willReturn('foo');
        $event->getSubject()->willReturn($group);

        $this->preUpdateGroup($event);
    }

    function it_throw_an_exception_when_deleting_default_group(GenericEvent $event, Group $group)
    {
        $group->getName()->willReturn(User::GROUP_DEFAULT);
        $event->getSubject()->willReturn($group);

        $this->shouldThrow('\Exception')->during('preDeleteGroup', [$event]);
    }

    function it_throw_an_exception_when_updating_default_group(GenericEvent $event, Group $group)
    {
        $group->getName()->willReturn(User::GROUP_DEFAULT);
        $event->getSubject()->willReturn($group);

        $this->shouldThrow('\Exception')->during('preUpdateGroup', [$event]);
    }
}
