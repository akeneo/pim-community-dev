<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Notification;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Notification\ProjectFinishedNotifier;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Notification\ProjectNotificationFactory;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectCompleteness;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectStatusInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Notification\ProjectNotifierInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectStatusRepositoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;

class ProjectFinishedNotifierSpec extends ObjectBehavior
{
    function let(
        ProjectNotificationFactory $projectNotificationFactory,
        NotifierInterface $notifier,
        PresenterInterface $datePresenter,
        ProjectStatusRepositoryInterface $projectStatusRepository
    ) {
        $this->beConstructedWith($projectNotificationFactory, $notifier, $datePresenter, $projectStatusRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectFinishedNotifier::class);
    }

    function it_is_a_notifier()
    {
        $this->shouldImplement(ProjectNotifierInterface::class);
    }

    function it_does_not_notify_users_if_a_project_pass_from_complete_to_in_progress(
        $projectStatusRepository,
        UserInterface $owner,
        ProjectInterface $project,
        ProjectStatusInterface $projectStatus,
        ProjectCompleteness $projectCompleteness
    ) {
        $projectStatusRepository->findProjectStatus($project, $owner)->willReturn($projectStatus);
        $projectCompleteness->isComplete()->willReturn(false);
        $projectStatus->isComplete()->willReturn(true);

        $this->notifyUser($owner, $project, $projectCompleteness)->shouldReturn(false);
    }

    function it_notifies_owner_that_a_project_is_finished(
        $projectStatusRepository,
        $projectNotificationFactory,
        $notifier,
        $datePresenter,
        NotificationInterface $notification,
        UserInterface $user,
        UserInterface $owner,
        ProjectInterface $project,
        ProjectStatusInterface $projectStatus,
        ProjectCompleteness $projectCompleteness,
        LocaleInterface $locale
    ) {
        $projectStatusRepository->findProjectStatus($project, $user)->willReturn($projectStatus);
        $projectCompleteness->isComplete()->willReturn(true);
        $projectStatus->isComplete()->willReturn(false);
        $project->getOwner()->willReturn($owner);
        $project->getDueDate()->willReturn('01/12/2030');
        $owner->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $datePresenter->present('01/12/2030', ['locale' => 'en_US'])->willReturn('12/01/2030');
        $project->getLabel()->willReturn('Project label');
        $project->getCode()->willReturn('project-code');

        $project->getOwner()->willReturn($owner);
        $user->getUserIdentifier()->willReturn('claude');
        $owner->getUserIdentifier()->willReturn('claude');

        $context = [
            'actionType'  => 'project_finished',
            'buttonLabel' => 'teamwork_assistant.notification.project_finished.show',
        ];

        $parameters = [
            '%project_label%' => '"Project label"',
            '%due_date%'      => '"12/01/2030"',
            'project_code'    => 'project-code',
        ];

        $projectNotificationFactory->create(
            ['identifier' => $parameters['project_code'], 'status' => 'all'],
            $parameters,
            $context,
            'teamwork_assistant.notification.project_finished.owner'
        )->willReturn($notification);

        $notifier->notify($notification, [$user])->shouldBeCalled();

        $this->notifyUser($user, $project, $projectCompleteness)->shouldReturn(true);
    }

    function it_notifies_contributors_that_a_project_is_finished(
        $projectStatusRepository,
        $projectNotificationFactory,
        $notifier,
        $datePresenter,
        NotificationInterface $notification,
        UserInterface $contributor,
        UserInterface $owner,
        ProjectInterface $project,
        ProjectStatusInterface $projectStatus,
        ProjectCompleteness $projectCompleteness,
        LocaleInterface $locale
    ) {
        $projectStatusRepository->findProjectStatus($project, $contributor)->willReturn($projectStatus);
        $projectCompleteness->isComplete()->willReturn(true);
        $projectStatus->isComplete()->willReturn(false);
        $project->getOwner()->willReturn($owner);
        $owner->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $datePresenter->present('01/12/2030', ['locale' => 'en_US'])->willReturn('12/01/2030');
        $project->getLabel()->willReturn('Project label');
        $project->getCode()->willReturn('project-code');
        $project->getDueDate()->willReturn('01/12/2030');

        $contributor->getUserIdentifier()->willReturn('boby');
        $owner->getUserIdentifier()->willReturn('claude');
        $project->getOwner()->willReturn($owner);

        $context = [
            'actionType'  => 'project_finished',
            'buttonLabel' => 'teamwork_assistant.notification.project_finished.show',
        ];

        $parameters = [
            '%project_label%' => '"Project label"',
            '%due_date%'      => '"12/01/2030"',
            'project_code'    => 'project-code',
        ];

        $projectNotificationFactory->create(
            ['identifier' => 'project-code', 'status' => 'all'],
            $parameters,
            $context,
            'teamwork_assistant.notification.project_finished.contributor'
        )->willReturn($notification);

        $notifier->notify($notification, [$contributor])->shouldBeCalled();

        $this->notifyUser($contributor, $project, $projectCompleteness)->shouldReturn(true);
    }
}
