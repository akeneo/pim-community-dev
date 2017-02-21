<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use Akeneo\Component\Localization\Presenter\DatePresenter;
use Pim\Bundle\NotificationBundle\Entity\NotificationInterface;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectNotificationFactory;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectCreatedNotifier;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectStatusInterface;
use PimEnterprise\Component\ActivityManager\Notification\ProjectNotifierInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectStatusRepositoryInterface;

class ProjectCreatedNotifierSpec extends ObjectBehavior
{
    function let(
        ProjectNotificationFactory $projectNotificationFactory,
        NotifierInterface $notifier,
        DatePresenter $datePresenter,
        ProjectStatusRepositoryInterface $projectStatusRepository
    ) {
        $this->beConstructedWith($projectNotificationFactory, $notifier, $datePresenter, $projectStatusRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectCreatedNotifier::class);
    }

    function it_is_a_notifier()
    {
        $this->shouldImplement(ProjectNotifierInterface::class);
    }

    function it_does_not_notify_owner_that_a_project_is_created(
        $projectStatusRepository,
        UserInterface $owner,
        ProjectInterface $project,
        ProjectStatusInterface $projectStatus,
        ProjectCompleteness $projectCompleteness
    ) {
        $projectStatusRepository->findProjectStatus($project, $owner)->willReturn($projectStatus);
        $projectStatus->hasBeenNotified()->willReturn(true);
        $projectCompleteness->isComplete()->willReturn(true);

        $this->notifyUser($owner, $project, $projectCompleteness)->shouldReturn(false);
    }

    function it_notifies_contributors_that_a_project_is_created(
        $projectNotificationFactory,
        $projectStatusRepository,
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
        $projectCompleteness->isComplete()->willReturn(false);
        $projectStatus->hasBeenNotified()->willReturn(false);

        $project->getOwner()->willReturn($owner);
        $contributor->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en_US');
        $datePresenter->present('01/12/2030', ['locale' => 'en_US'])->willReturn('12/01/2030');
        $project->getLabel()->willReturn('Project label');
        $project->getCode()->willReturn('project-code');
        $project->getDueDate()->willReturn('01/12/2030');

        $contributor->getUsername()->willReturn('boby');
        $owner->getUsername()->willReturn('claude');
        $project->getOwner()->willReturn($owner);

        $context = [
            'actionType'  => 'project_created',
            'buttonLabel' => 'activity_manager.notification.project_calculation.start'
        ];

        $parameters = ['%project_label%' => 'Project label', '%due_date%' => '12/01/2030'];

        $projectNotificationFactory->create(
            ['identifier' => 'project-code'],
            $parameters,
            $context,
            'activity_manager.notification.message'
        )->willReturn($notification);

        $notifier->notify($notification, [$contributor])->shouldBeCalled();

        $this->notifyUser($contributor, $project, $projectCompleteness)->shouldReturn(true);
    }
}
