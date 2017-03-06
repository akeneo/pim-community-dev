<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\EventListener;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\TeamworkAssistantBundle\EventListener\NotificationSubscriber;
use PimEnterprise\Component\TeamworkAssistant\Event\ProjectEvent;
use PimEnterprise\Component\TeamworkAssistant\Event\ProjectEvents;
use PimEnterprise\Component\TeamworkAssistant\Factory\ProjectStatusFactoryInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectCompleteness;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectStatusInterface;
use PimEnterprise\Component\TeamworkAssistant\Notification\ProjectNotifierInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectStatusRepositoryInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\UserRepositoryInterface;
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
