<?php

namespace spec\Akeneo\ActivityManager\Bundle\Job;

use Akeneo\ActivityManager\Bundle\Doctrine\ORM\Repository\JobInstanceRepository;
use Akeneo\ActivityManager\Component\Job\ProjectCalculation\ProjectCalculationJobLauncherInterface;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;

class ProjectCalculationJobLauncherSpec extends ObjectBehavior
{
    function let(JobLauncherInterface $simpleJobLauncher, JobInstanceRepository $jobInstanceRepository)
    {
        $this->beConstructedWith($simpleJobLauncher, $jobInstanceRepository);
    }

    function it_is_a_project_job_launcher()
    {
        $this->shouldImplement(ProjectCalculationJobLauncherInterface::class);
    }

    function it_launches_a_project_calculation_job(
        $simpleJobLauncher,
        $jobInstanceRepository,
        UserInterface $user,
        ProjectInterface $project,
        JobInstance $jobInstance
    ) {
        $jobInstanceRepository->getProjectCalculation()->willReturn($jobInstance);

        $project->getCode()->willReturn('project_code');

        $configuration = ['project_code' => 'project_code'];

        $simpleJobLauncher->launch($jobInstance, $user, $configuration)->shouldBeCalled();

        $this->launch($user, $project);
    }
}
