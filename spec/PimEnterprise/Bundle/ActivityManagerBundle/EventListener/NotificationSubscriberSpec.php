<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\EventListener;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\EventListener\NotificationSubscriber;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvent;
use PimEnterprise\Component\ActivityManager\Event\ProjectEvents;
use PimEnterprise\Component\ActivityManager\Factory\ProjectStatusFactoryInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectStatusInterface;
use PimEnterprise\Component\ActivityManager\Notification\ProjectNotifierInterface;
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

    function it_notifies_users(
        $projectStatusRepository,
        $userRepository,
        $projectCompletenessRepository,
        $projectStatusFactory,
        $projectFinishedNotifier,
        $projectCreatedNotifier,
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

        $projectCompleteness->isComplete()->willReturn(false);
        $projectStatusFactory->create($project, $user)->willReturn($projectStatus);
        $projectStatus->setHasBeenNotified(false)->shouldBeCalled();
        $projectStatus->setIsComplete(false)->shouldBeCalled();

        $projectCreatedNotifier->notifyUser($user, $project, $projectCompleteness)->willReturn(true);
        $projectStatus->setHasBeenNotified(true)->shouldBeCalled();
        $projectStatus->setIsComplete(false)->shouldBeCalled();
        $projectStatusSaver->save($projectStatus)->shouldBeCalled();

        $projectFinishedNotifier->notifyUser($user, $project, $projectCompleteness)->willReturn(true);

        $this->notify($event);
    }
}
