<?php

namespace spec\Akeneo\ActivityManager\Bundle\EventSubscriber;

use Akeneo\ActivityManager\Bundle\EventSubscriber\JobExecutionNotifier;
use Akeneo\ActivityManager\Bundle\Factory\ProjectCreatedNotificationFactory;
use Akeneo\ActivityManager\Component\Event\ProjectEvent;
use Akeneo\ActivityManager\Component\Event\ProjectEvents;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Akeneo\ActivityManager\Component\Repository\UserRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JobExecutionNotifierSpec extends ObjectBehavior
{
    function let(
        ProjectCreatedNotificationFactory $factory,
        NotifierInterface $notifier,
        ProjectRepositoryInterface $projectRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->beConstructedWith($factory, $notifier, $projectRepository, $userRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(JobExecutionNotifier::class);
    }

    function it_is_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            ProjectEvents::PROJECT_CALCULATED => 'projectCreated',
        ]);
    }

    function it_notifies_users_when_the_project_is_created(
        $factory,
        $userRepository,
        $notifier,
        ProjectEvent $event,
        ProjectInterface $project,
        DatagridView $view,
        Group $userGroup,
        UserInterface $user,
        NotificationInterface $notification
    ) {
        $userGroups = [$userGroup];
        $event->getProject()->willReturn($project);
        $project->getDatagridView()->willReturn($view);
        $view->getFilters()->willReturn('filters');
        $project->getUserGroups()->willReturn($userGroups);
        $project->getOwner()->willReturn($user);

        $user->getId()->willReturn(42);
        $userGroup->getId()->willReturn(84);

        $factory->create('filters')->willReturn($notification);

        $userRepository->findByGroupIdsOwnerExcluded(42, [84])->willReturn([$user]);
        $notifier->notify($notification, [$user])->shouldBeCalled();

        $this->projectCreated($event)->shouldReturn(null);
    }
}
