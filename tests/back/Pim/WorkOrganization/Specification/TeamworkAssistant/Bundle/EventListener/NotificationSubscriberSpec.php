<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\EventListener;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\EventListener\NotificationSubscriber;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Event\ProjectEvent;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Event\ProjectEvents;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Factory\ProjectStatusFactoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectCompleteness;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectStatusInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Notification\ProjectNotifierInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectCompletenessRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectStatusRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
