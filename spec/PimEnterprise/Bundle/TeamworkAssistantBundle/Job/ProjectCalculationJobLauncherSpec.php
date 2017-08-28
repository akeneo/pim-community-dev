<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Job;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;

class ProjectCalculationJobLauncherSpec extends ObjectBehavior
{
    function let(JobLauncherInterface $simpleJobLauncher, JobInstanceRepository $jobInstanceRepository)
    {
        $this->beConstructedWith($simpleJobLauncher, $jobInstanceRepository, 'job_name');
    }

    function it_launches_a_project_calculation_job(
        $simpleJobLauncher,
        $jobInstanceRepository,
        UserInterface $user,
        ProjectInterface $project,
        JobInstance $jobInstance
    ) {
        $jobInstanceRepository->findOneByIdentifier('job_name')->willReturn($jobInstance);

        $project->getCode()->willReturn('project_code');
        $project->getOwner()->willReturn($user);
        $user->getUsername()->willReturn('julia');

        $configuration = [
            'project_code' => 'project_code',
            'notification_user' => 'julia'
        ];

        $simpleJobLauncher->launch($jobInstance, $user, $configuration)->shouldBeCalled();

        $this->launch($project);
    }


    function it_throws_an_exception_if_the_job_instance_does_not_exist(
        $jobInstanceRepository,
        ProjectInterface $project
    ) {
        $jobInstanceRepository->findOneByIdentifier('job_name')->willReturn(null);

        $this->shouldThrow(\RuntimeException::class)->during('launch', [$project]);
    }
}
