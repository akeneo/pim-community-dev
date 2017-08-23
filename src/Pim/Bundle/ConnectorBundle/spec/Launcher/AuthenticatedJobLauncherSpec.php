<?php

namespace spec\Pim\Bundle\ConnectorBundle\Launcher;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use PhpSpec\ObjectBehavior;

class AuthenticatedJobLauncherSpec extends ObjectBehavior
{
    function let(
        JobRepositoryInterface $jobRepository,
        JobParametersFactory $jobParametersFactory,
        JobRegistry $jobRegistry
    ) {
        $this->beConstructedWith($jobRepository, $jobParametersFactory, $jobRegistry, '/', 'prod', '/logs');
    }

    function it_is_a_job_launcher()
    {
        $this->shouldHaveType(JobLauncherInterface::class);
    }
}
