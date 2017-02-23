<?php

namespace spec\PimEnterprise\Bundle\TeamWorkAssistantBundle\Command;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use PimEnterprise\Bundle\TeamWorkAssistantBundle\Command\ProjectRecalculationCommand;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\TeamWorkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamWorkAssistant\Repository\ProjectRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProjectRecalculationCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectRecalculationCommand::class);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pimee:project:recalculate');
    }

    function it_is_a_container_aware_command()
    {
        $this->shouldHaveType(ContainerAwareCommand::class);
    }

    function it_recalculated_all_project(
        ContainerInterface $container,
        InputInterface $input,
        OutputInterface $output,
        ProjectRepositoryInterface $projectRepository,
        CursorInterface $projects,
        ObjectDetacherInterface $objectDetacher,
        ProjectInterface $project,
        ProjectInterface $otherProject,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition
    ) {
        $container->get('pimee_team_work_assistant.repository.project')->willReturn($projectRepository);
        $container->get('akeneo_storage_utils.doctrine.object_detacher')->willReturn($objectDetacher);
        $container->getParameter('pimee_team_work_assistant.project_calculation.job_name')
            ->willReturn('project_calculation');

        $projectRepository->findAll()->willReturn($projects);

        $projects->rewind()->shouldBeCalled();
        $projects->valid()->willReturn(true, true, false);
        $projects->current()->willReturn($project, $otherProject);
        $projects->next()->shouldBeCalled();

        $project->getCode()->willReturn('project-code');
        $commandInput = new ArrayInput([
            'command' => 'akeneo:batch:job',
            'code' => 'project_calculation',
            '-c' => '{"project_code":"project-code"}',
            '--no-debug' => true,
        ]);
        $application->run($commandInput, $output)->willReturn(0);
        $objectDetacher->detach($project)->shouldBeCalled();

        $otherProject->getCode()->willReturn('other-project-code');
        $commandInput = new ArrayInput([
            'command' => 'akeneo:batch:job',
            'code' => 'project_calculation',
            '-c' => '{"project_code":"other-project-code"}',
            '--no-debug' => true,
        ]);
        $application->run($commandInput, $output)->willReturn(0);

        $definition->getOptions()->willReturn([]);
        $definition->getArguments()->willReturn([]);

        $application->getHelperSet()->willReturn($helperSet);
        $application->getDefinition()->willReturn($definition);
        $application->setAutoExit(false)->shouldBeCalled();

        $this->setApplication($application);
        $this->setContainer($container);
        $this->run($input, $output);
    }
}
