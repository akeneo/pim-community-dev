<?php

namespace spec\PimEnterprise\Bundle\TeamWorkAssistantBundle\Command;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\TeamWorkAssistantBundle\Command\NotificationDueDateWarningCommand;
use PimEnterprise\Component\TeamWorkAssistant\Model\ProjectCompleteness;
use PimEnterprise\Component\TeamWorkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamWorkAssistant\Notification\ProjectNotifierInterface;
use PimEnterprise\Component\TeamWorkAssistant\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\TeamWorkAssistant\Repository\ProjectRepositoryInterface;
use PimEnterprise\Component\TeamWorkAssistant\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationDueDateWarningCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NotificationDueDateWarningCommand::class);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee:project:notify-before-due-date');
    }

    function it_is_a_container_aware_command()
    {
        $this->shouldHaveType(ContainerAwareCommand::class);
    }

    function it_notify_users(
        ContainerInterface $container,
        OutputInterface $output,
        ProjectRepositoryInterface $projectRepository,
        ProjectInterface $project,
        UserRepositoryInterface $userRepository,
        ProjectNotifierInterface $projectDueDateReminderNotifier,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        ProjectInterface $otherProject,
        ProjectCompleteness $projectCompleteness,
        UserInterface $user,
        Application $application
    ) {
        $container->get('pimee_team_work_assistant.repository.project')->willReturn($projectRepository);
        $container->get('pimee_team_work_assistant.notifier.project_due_date_reminder')
            ->willReturn($projectDueDateReminderNotifier);
        $container->get('pimee_team_work_assistant.repository.project_completeness')
            ->willReturn($projectCompletenessRepository);
        $container->get('pimee_team_work_assistant.repository.user')->willReturn($userRepository);

        $projectRepository->findAll()->willReturn([$project]);
        $userRepository->findUsersToNotify($project)->willReturn([$user]);
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);
        $projectCompleteness->getRatioForDone()->willReturn(30);
        $projectCompleteness->isComplete()->willReturn(true);
        $projectDueDateReminderNotifier->notifyUser($user, $project, $projectCompleteness)->willReturn(true);

        $commandInput = new ArrayInput(
            [
                'command'    => 'pimee:project:notify-before-due-date',
                '--no-debug' => true,
            ]
        );
        $application->run($commandInput, $output)->willReturn(0);

        $otherProject->getCode()->willReturn('other-project-code');

        $application->run($commandInput, $output)->willReturn(0);
    }
}
