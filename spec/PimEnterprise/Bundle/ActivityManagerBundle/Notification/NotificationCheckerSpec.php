<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\NotificationChecker;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\NotificationCheckerInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectStatusRepositoryInterface;

class NotificationCheckerSpec extends ObjectBehavior
{
    function let(
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        ProjectStatusRepositoryInterface $projectStatusRepository
    ) {
        $this->beConstructedWith($projectCompletenessRepository, $projectStatusRepository);
    }

    function it_is_a_checker()
    {
        $this->shouldHaveType(NotificationChecker::class);
    }

    function it_implements_notification_checker_interface()
    {
        $this->shouldImplement(NotificationCheckerInterface::class);
    }

    function it_is_notifiable_for_product_creation(
        $projectCompletenessRepository,
        ProjectInterface $project,
        UserInterface $user,
        ProjectCompleteness $projectCompleteness
    ) {
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);
        $projectCompleteness->isComplete()->willReturn(false);
        $project->isCreated()->willReturn(false);

        $this->isNotifiableForProjectCreation($project, $user)->shouldReturn(true);
    }

    function it_is_not_notifiable_for_product_creation_if_product_already_created(
        $projectCompletenessRepository,
        ProjectInterface $project,
        UserInterface $user,
        ProjectCompleteness $projectCompleteness
    ) {
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);
        $projectCompleteness->isComplete()->willReturn(false);
        $project->isCreated()->willReturn(true);

        $this->isNotifiableForProjectCreation($project, $user)->shouldReturn(false);
    }

    function it_is_not_notifiable_for_product_creation_if_product_is_already_complete(
        $projectCompletenessRepository,
        ProjectInterface $project,
        UserInterface $user,
        ProjectCompleteness $projectCompleteness
    ) {
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);
        $projectCompleteness->isComplete()->willReturn(true);
        $project->isCreated()->willReturn(false);

        $this->isNotifiableForProjectCreation($project, $user)->shouldReturn(false);
    }

    function it_is_not_notifiable_for_product_finished_if_it_is_already_complete_and_was_complete(
        $projectCompletenessRepository,
        $projectStatusRepository,
        ProjectInterface $project,
        UserInterface $user,
        ProjectCompleteness $projectCompleteness
    ) {
        $projectStatusRepository->wasComplete($project, $user)->willReturn(true);
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);
        $projectCompleteness->isComplete()->willReturn(true);

        $this->isNotifiableForProjectFinished($project, $user)->shouldReturn(false);
    }

    function it_is_not_notifiable_for_product_finished_is_not_complete_but_was_complete(
        $projectCompletenessRepository,
        $projectStatusRepository,
        ProjectInterface $project,
        UserInterface $user,
        ProjectCompleteness $projectCompleteness
    ) {
        $projectStatusRepository->wasComplete($project, $user)->willReturn(true);
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);
        $projectCompleteness->isComplete()->willReturn(false);
        $projectStatusRepository->setProjectStatus($project, $user, false)->shouldBeCalled();

        $this->isNotifiableForProjectFinished($project, $user)->shouldReturn(false);
    }

    function it_is_not_notifiable_for_product_finished_is_not_complete_and_was_not_complete(
        $projectCompletenessRepository,
        $projectStatusRepository,
        ProjectInterface $project,
        UserInterface $user,
        ProjectCompleteness $projectCompleteness
    ) {
        $projectStatusRepository->wasComplete($project, $user)->willReturn(false);
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);
        $projectCompleteness->isComplete()->willReturn(false);

        $this->isNotifiableForProjectFinished($project, $user)->shouldReturn(false);
    }

    function it_notifiable_for_product_finished_if_it_is_complete_and_was_not_complete(
        $projectCompletenessRepository,
        $projectStatusRepository,
        ProjectInterface $project,
        UserInterface $user,
        ProjectCompleteness $projectCompleteness
    ) {
        $project->isCreated()->willReturn(false);
        $projectStatusRepository->wasComplete($project, $user)->willReturn(false);
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);
        $projectCompleteness->isComplete()->willReturn(true);
        $projectStatusRepository->setProjectStatus($project, $user, true)->shouldBeCalled();

        $this->isNotifiableForProjectFinished($project, $user)->shouldReturn(true);
    }

    function it_is_not_notifiable_for_product_finished_if_it_is_complete_and_on_project_creation(
        $projectCompletenessRepository,
        $projectStatusRepository,
        ProjectInterface $project,
        UserInterface $user,
        ProjectCompleteness $projectCompleteness
    ) {
        $project->isCreated()->willReturn(true);
        $projectStatusRepository->wasComplete($project, $user)->willReturn(false);
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);
        $projectCompleteness->isComplete()->willReturn(true);

        $this->isNotifiableForProjectFinished($project, $user)->shouldReturn(false);
    }
}
