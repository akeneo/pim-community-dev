<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Command;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\Command\NotificationDueDateWarningCommand;
use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectDueDateNotifierInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\UserRepositoryInterface;
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
        $this->getName()->shouldReturn('pim:activity_manager:due_date');
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
        ProjectDueDateNotifierInterface $projectDueDateNotifier,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        ProjectInterface $otherProject,
        ProjectCompleteness $projectCompleteness,
        UserInterface $user,
        Application $application
    ) {
        $container->get('pimee_activity_manager.repository.project')->willReturn($projectRepository);
        $container->get('pimee_activity_manager.notifier.project_due_date')->willReturn($projectDueDateNotifier);
        $container->get('pimee_activity_manager.repository.project_completeness')
            ->willReturn($projectCompletenessRepository);
        $container->get('pimee_activity_manager.repository.user')->willReturn($userRepository);

        $projectRepository->findAll()->willReturn([$project]);
        $userRepository->findUsersToNotify($project)->willReturn([$user]);
        $projectCompletenessRepository->getProjectCompleteness($project, $user)->willReturn($projectCompleteness);
        $projectCompleteness->getRatioForDone()->willReturn(30);
        $projectCompleteness->isComplete()->willReturn(true);
        $projectDueDateNotifier->notifyUser($user, $project, $projectCompleteness)->willReturn(true);

        $commandInput = new ArrayInput(
            [
                'command'    => 'pim:activity_manager:due_date',
                '--no-debug' => true,
            ]
        );
        $application->run($commandInput, $output)->willReturn(0);

        $otherProject->getCode()->willReturn('other-project-code');

        $application->run($commandInput, $output)->willReturn(0);
    }
}
