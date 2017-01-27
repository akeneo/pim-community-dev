<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\EventListener\ProjectFinishedNotifierSubscriber;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\NotificationChecker;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectFinishedNotificationFactory;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvent;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvents;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectStatusRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectFinishedNotifierSubscriberSpec extends ObjectBehavior
{
    function let(
        ProjectFinishedNotificationFactory $factory,
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        NotificationChecker $notificationChecker,
        ProjectStatusRepositoryInterface $projectStatusRepository,
        ProjectRepositoryInterface $projectRepository,
        SaverInterface $projectSaver
    ) {
        $this->beConstructedWith(
            $factory,
            $notifier,
            $userRepository,
            $notificationChecker,
            $projectStatusRepository,
            $projectRepository,
            $projectSaver
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectFinishedNotifierSubscriber::class);
    }

    function it_is_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            ProjectEvents::PROJECT_CALCULATED => 'projectFinished',
        ]);
    }

    function it_notifies_the_owner_and_not_the_contributors_when_the_project_is_notifiable(
        $factory,
        $notifier,
        $projectSaver,
        $notificationChecker,
        $projectStatusRepository,
        $userRepository,
        ProjectEvent $event,
        ProjectInterface $project,
        UserInterface $owner,
        NotificationInterface $notification
    ) {
        $event->getProject()->willReturn($project);
        $project->getOwner()->willReturn($owner);
        $notificationChecker->isNotifiableForProjectFinished($project, $owner)->willReturn(true);
        $project->setIsCreated(true)->shouldBeCalled();
        $projectSaver->save($project)->shouldBeCalled();
        $factory->create($project, 'activity_manager.notification.project_finished.owner')->willReturn($notification);
        $notifier->notify($notification, [$owner])->shouldBeCalled();
        $projectStatusRepository->setProjectStatus($project, $owner, true)->shouldBeCalled();
        $userRepository->findContributorsToNotify($project)->willReturn([]);

        $this->projectFinished($event)->shouldReturn(null);
    }

    function it_does_not_notifies_the_owner_and_not_the_contributors_if_the_project_is_not_notifiable(
        $notifier,
        $notificationChecker,
        $userRepository,
        ProjectEvent $event,
        ProjectInterface $project,
        UserInterface $owner,
        UserInterface $contributor
    ) {
        $event->getProject()->willReturn($project);
        $project->getOwner()->willReturn($owner);
        $notificationChecker->isNotifiableForProjectFinished($project, $owner)->willReturn(false);
        $userRepository->findContributorsToNotify($project)->willReturn([$contributor]);
        $notificationChecker->isNotifiableForProjectFinished($project, $contributor)->willReturn(false);
        $notifier->notify(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->projectFinished($event)->shouldReturn(null);
    }

    function it_notifies_contributors_and_not_the_owner_when_the_project_is_notifiable(
        $factory,
        $notifier,
        $notificationChecker,
        $projectStatusRepository,
        $userRepository,
        ProjectEvent $event,
        ProjectInterface $project,
        UserInterface $owner,
        UserInterface $contributor,
        NotificationInterface $notification
    ) {
        $event->getProject()->willReturn($project);
        $project->getOwner()->willReturn($owner);
        $notificationChecker->isNotifiableForProjectFinished($project, $owner)->willReturn(false);
        $userRepository->findContributorsToNotify($project)->willReturn([$contributor,]);
        $notificationChecker->isNotifiableForProjectFinished($project, $contributor)->willReturn(true);
        $factory->create($project, 'activity_manager.notification.project_finished.contributor')
            ->willReturn($notification);
        $notifier->notify($notification, [$contributor])->shouldBeCalled();
        $projectStatusRepository->setProjectStatus($project, $contributor, true)->shouldBeCalled();

        $this->projectFinished($event)->shouldReturn(null);
    }

    function it_does_not_notifies_contributors_and_not_the_owner_when_the_project_is_not_notifiable(
        $notifier,
        $notificationChecker,
        $projectStatusRepository,
        $userRepository,
        ProjectEvent $event,
        ProjectInterface $project,
        UserInterface $owner,
        UserInterface $contributor
    ) {
        $event->getProject()->willReturn($project);
        $project->getOwner()->willReturn($owner);
        $notificationChecker->isNotifiableForProjectFinished($project, $owner)->willReturn(false);
        $userRepository->findContributorsToNotify($project)->willReturn([$contributor,]);
        $notificationChecker->isNotifiableForProjectFinished($project, $contributor)->willReturn(false);
        $notifier->notify(Argument::any(), Argument::any())->shouldNotBeCalled();
        $projectStatusRepository->setProjectStatus($project, $contributor, true)->shouldNotBeCalled();

        $this->projectFinished($event)->shouldReturn(null);
    }
}
