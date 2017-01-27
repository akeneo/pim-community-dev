<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\EventListener\ProjectCreationNotifierSubscriber;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\NotificationChecker;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectCreatedNotificationFactory;
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

class ProjectCreationNotifierSubscriberSpec extends ObjectBehavior
{
    function let(
        ProjectCreatedNotificationFactory $notificationFactory,
        NotifierInterface $notifier,
        UserRepositoryInterface $userRepository,
        NotificationChecker $notificationChecker,
        ProjectStatusRepositoryInterface $projectStatusRepository,
        ProjectRepositoryInterface $projectRepository,
        SaverInterface $projectSaver
    ) {
        $this->beConstructedWith(
            $notificationFactory,
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
        $this->shouldHaveType(ProjectCreationNotifierSubscriber::class);
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
        $notificationFactory,
        $userRepository,
        $notifier,
        $projectStatusRepository,
        $notificationChecker,
        $projectSaver,
        ProjectEvent $event,
        ProjectInterface $project,
        UserInterface $user,
        NotificationInterface $notification
    ) {
        $event->getProject()->willReturn($project);
        $userRepository->findContributorsToNotify($project)->willReturn([$user]);
        $notificationChecker->isNotifiableForProjectCreation($project, $user)->willReturn(true);
        $notificationFactory->create($project, $user)->willReturn($notification);
        $notifier->notify($notification, [$user])->shouldBeCalled();
        $projectStatusRepository->setProjectStatus($project, $user, false)->shouldBeCalled();
        $project->setIsCreated(true)->shouldBeCalled();
        $projectSaver->save($project)->shouldBeCalled();


        $this->projectCreated($event)->shouldReturn(null);
    }

    function it_does_not_notify_users_when_the_project_is_created_and_already_to_100_percent_done(
        $notificationFactory,
        $userRepository,
        $notifier,
        $notificationChecker,
        ProjectEvent $event,
        ProjectInterface $project,
        UserInterface $user
    ) {
        $event->getProject()->willReturn($project);
        $userRepository->findContributorsToNotify($project)->willReturn([$user]);
        $notificationChecker->isNotifiableForProjectCreation($project, $user)->willReturn(false);
        $notificationFactory->create(Argument::any(), Argument::any())->shouldNotBeCalled();
        $notifier->notify(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->projectCreated($event)->shouldReturn(null);
    }
}
