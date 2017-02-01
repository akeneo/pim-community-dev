<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\EventListener\NotificationSubscriber;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectNotifierInterface;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvent;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvents;
use PimEnterprise\Component\ActivityManager\Factory\ProjectStatusFactoryInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectStatus;
use PimEnterprise\Component\ActivityManager\Model\ProjectStatusInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectStatusRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationSubscriberSpec extends ObjectBehavior
{
    function let(
        UserRepositoryInterface $userRepository,
        ProjectStatusFactoryInterface $projectStatusFactory,
        ProjectStatusRepositoryInterface $projectStatusRepository,
        SaverInterface $projectStatusSaver,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        ProjectNotifierInterface $projectCreatedNotifier,
        ProjectNotifierInterface $projectFinishedNotifier
    ) {
        $this->beConstructedWith(
            $userRepository,
            $projectStatusFactory,
            $projectStatusRepository,
            $projectStatusSaver,
            $projectCompletenessRepository,
            $projectCreatedNotifier,
            $projectFinishedNotifier
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NotificationSubscriber::class);
    }

    function it_is_a_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn([
            ProjectEvents::PROJECT_CALCULATED => 'notify',
        ]);
    }

    function it_notifies_users_once_project_is_created_and_not_complete(
        $userRepository,
        $projectCompletenessRepository,
        $projectStatusRepository,
        $projectCreatedNotifier,
        $projectStatusFactory,
        $projectFinishedNotifier,
        $projectStatusSaver,
        UserInterface $user,
        ProjectEvent $event,
        ProjectInterface $project,
        ProjectCompleteness $projectCompleteness,
        ProjectStatusInterface $projectStatus
    ) {
        $event->getProject()->willReturn($project);
        $userRepository->findUsersToNotify($project)->willReturn([$user]);
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);
        $projectStatusRepository->findProjectStatus($project, $user)->willReturn(null);

        $projectStatusFactory->create($project, $user)->willReturn($projectStatus);
        $projectCompleteness->isComplete()->willReturn(false);
        $projectStatus->setIsComplete(false)->shouldBeCalled();
        $projectStatus->setHasBeenNotified(false)->shouldBeCalled();
        $projectStatus->hasBeenNotified()->willReturn(false);
        $projectStatus->setIsComplete(false)->shouldBeCalled();

        $projectCreatedNotifier->notifyUser($user, $project)->shouldBeCalled();
        $projectFinishedNotifier->notifyUser($user, $project)->shouldNotBeCalled();
        $projectStatus->setHasBeenNotified(true)->shouldBeCalled();
        $projectStatus->setIsComplete(false)->shouldBeCalled();

        $projectStatusSaver->save($projectStatus)->shouldBeCalled();

        $this->notify($event);
    }

    function it_notifies_users_once_project_is_finished(
        $userRepository,
        $projectCompletenessRepository,
        $projectStatusRepository,
        $projectCreatedNotifier,
        $projectFinishedNotifier,
        $projectStatusSaver,
        UserInterface $user,
        ProjectEvent $event,
        ProjectInterface $project,
        ProjectCompleteness $projectCompleteness,
        ProjectStatusInterface $projectStatus
    ) {
        $event->getProject()->willReturn($project);
        $userRepository->findUsersToNotify($project)->willReturn([$user]);
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);
        $projectStatusRepository->findProjectStatus($project, $user)->willReturn($projectStatus);
        $projectStatus->hasBeenNotified()->willReturn(true);

        $projectCompleteness->isComplete()->willReturn(true);
        $projectStatus->isComplete()->willReturn(false);
        $projectStatus->setIsComplete(true)->shouldBeCalled();

        $projectCreatedNotifier->notifyUser($user, $project)->shouldNotBeCalled();
        $projectFinishedNotifier->notifyUser($user, $project)->shouldBeCalled();

        $projectStatusSaver->save($projectStatus)->shouldBeCalled();

        $this->notify($event);
    }

    function it_does_not_notify_users_once_project_is_finished(
        $userRepository,
        $projectCompletenessRepository,
        $projectStatusRepository,
        $projectCreatedNotifier,
        $projectFinishedNotifier,
        $projectStatusSaver,
        UserInterface $user,
        ProjectEvent $event,
        ProjectInterface $project,
        ProjectCompleteness $projectCompleteness,
        ProjectStatusInterface $projectStatus
    ) {
        $event->getProject()->willReturn($project);
        $userRepository->findUsersToNotify($project)->willReturn([$user]);
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);
        $projectStatusRepository->findProjectStatus($project, $user)->willReturn($projectStatus);
        $projectStatus->hasBeenNotified()->willReturn(true);
        $projectStatus->isComplete()->willReturn(true);

        $projectCompleteness->isComplete()->willReturn(false);
        $projectStatus->setIsComplete(false)->shouldBeCalled();

        $projectCreatedNotifier->notifyUser($user, $project)->shouldNotBeCalled();
        $projectFinishedNotifier->notifyUser($user, $project)->shouldNotBeCalled();

        $projectStatusSaver->save($projectStatus)->shouldBeCalled();

        $this->notify($event);
    }
}
