<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\EventListener;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\EventListener\EnsureUserCanBeDeletedSubscriber;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Query\IsUserLinkedToProjectsQueryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Query\IsUserOwnerOfProjectsQueryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\UserBundle\Exception\UserCannotBeDeletedException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class EnsureUserCanBeDeletedSubscriberSpec extends ObjectBehavior
{
    function let(
        IsUserLinkedToProjectsQueryInterface $isUserLinkedToProjectsQuery,
        IsUserOwnerOfProjectsQueryInterface $isUserOwnerOfProjectsQuery
    ) {
        $this->beConstructedWith($isUserLinkedToProjectsQuery, $isUserOwnerOfProjectsQuery);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EnsureUserCanBeDeletedSubscriber::class);
    }

    function it_should_be_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_pre_remove_event()
    {
        $this->getSubscribedEvents()->shouldReturn([StorageEvents::PRE_REMOVE => 'ensureUserCanBeDeleted']);
    }

    function it_does_nothing_if_subject_is_not_a_user($isUserLinkedToProjectsQuery, GenericEvent $event)
    {
        $event->getSubject()->willReturn(null);
        $isUserLinkedToProjectsQuery->execute(Argument::any())->shouldNotBeCalled();
        $this->ensureUserCanBeDeleted($event)->shouldReturn(null);
    }

    function it_throws_an_exception_if_user_is_owner_of_a_project(
        IsUserLinkedToProjectsQueryInterface $isUserLinkedToProjectsQuery,
        IsUserOwnerOfProjectsQueryInterface $isUserOwnerOfProjectsQuery,
        GenericEvent $event,
        UserInterface $user
    ) {
        $event->getSubject()->willReturn($user);
        $user->getId()->willReturn(1);

        $isUserLinkedToProjectsQuery->execute(1)->shouldNotBeCalled();
        $isUserOwnerOfProjectsQuery->execute(1)->shouldBeCalled()->willReturn(true);

        $this->shouldThrow(UserCannotBeDeletedException::class)->during('ensureUserCanBeDeleted', [$event]);
    }

    function it_throws_an_exception_if_user_is_linked_to_a_project(
        IsUserLinkedToProjectsQueryInterface $isUserLinkedToProjectsQuery,
        IsUserOwnerOfProjectsQueryInterface $isUserOwnerOfProjectsQuery,
        GenericEvent $event,
        UserInterface $user
    ) {
        $event->getSubject()->willReturn($user);
        $user->getId()->willReturn(1);

        $isUserLinkedToProjectsQuery->execute(1)->shouldBeCalled()->willReturn(true);
        $isUserOwnerOfProjectsQuery->execute(1)->shouldBeCalled()->willReturn(false);

        $this->shouldThrow(UserCannotBeDeletedException::class)->during('ensureUserCanBeDeleted', [$event]);
    }

    function it_does_not_throw_an_exception_if_user_is_not_linked_to_or_owner_of_a_project(
        IsUserLinkedToProjectsQueryInterface $isUserLinkedToProjectsQuery,
        IsUserOwnerOfProjectsQueryInterface $isUserOwnerOfProjectsQuery,
        GenericEvent $event,
        UserInterface $user
    ) {
        $event->getSubject()->willReturn($user);
        $user->getId()->willReturn(1);

        $isUserLinkedToProjectsQuery->execute(1)->shouldBeCalled()->willReturn(false);
        $isUserOwnerOfProjectsQuery->execute(1)->shouldBeCalled()->willReturn(false);

        $this->ensureUserCanBeDeleted($event);
    }
}
