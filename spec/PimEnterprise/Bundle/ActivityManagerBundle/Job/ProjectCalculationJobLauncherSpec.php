<?php

namespace spec\Akeneo\ActivityManager\Bundle\Job;

use Akeneo\ActivityManager\Bundle\Doctrine\Repository\JobInstanceRepository;
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

        $project->getId()->willReturn(5);

        $configuration = ['project_id' => 5];

        $simpleJobLauncher->launch($jobInstance, $user, $configuration)->shouldBeCalled();

        $this->launch($user, $project);
    }
}
