<?php

declare(strict_types=1);

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\EventListener;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\TeamworkAssistantBundle\EventListener\DeleteProjectStatusIfUserIsNotLinkedToProjectSubscriber;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\Workflow\Query\DeleteProjectStatusIfUserIsNotLinkedToProject;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class DeleteProjectStatusIfUserIsNotLinkedToProjectSubscriberSpec extends ObjectBehavior
{
    function let(DeleteProjectStatusIfUserIsNotLinkedToProjectSpec $deleteProjectStatusIfUserIsNotLinkedToProject)
    {
        $this->beConstructedWith($deleteProjectStatusIfUserIsNotLinkedToProject);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DeleteProjectStatusIfUserIsNotLinkedToProjectSubscriber::class);
    }

    function it_should_be_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_pre_remove_event()
    {
        $this->getSubscribedEvents()->shouldReturn([StorageEvents::PRE_REMOVE => 'removeUserFromProject']);
    }

    function it_does_nothing_if_subject_is_not_a_user($deleteProjectStatusIfUserIsNotLinkedToProject, GenericEvent $event)
    {
        $event->getSubject()->willReturn(new \stdClass());

        $deleteProjectStatusIfUserIsNotLinkedToProject->__invoke(Argument::any())->shouldNotBeCalled();

        $this->removeUserFromProject($event)->shouldReturn(null);
    }

    function it_deletes_a_project_status_db_rows_if_a_user_is_not_linked_to_project(
        $deleteProjectStatusIfUserIsNotLinkedToProject,
        GenericEvent $event,
        UserInterface $user
    ) {
        $event->getSubject()->willReturn($user);
        $user->getId()->willReturn(1);

        $deleteProjectStatusIfUserIsNotLinkedToProject->__invoke(1)->shouldBeCalled();

        $this->removeUserFromProject($event)->shouldReturn(null);
    }
}

interface DeleteProjectStatusIfUserIsNotLinkedToProjectSpec extends DeleteProjectStatusIfUserIsNotLinkedToProject
{
    public function __invoke(int $userId): void;
}
