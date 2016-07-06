<?php

namespace spec\Akeneo\Bundle\BatchBundle\Launcher;

use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SimpleJobLauncherSpec extends ObjectBehavior
{
    function let(
        JobRepositoryInterface $jobRepository,
        JobParametersFactory $jobParametersFactory,
        JobRegistry $jobRegistry
    ) {
        $this->beConstructedWith($jobRepository, $jobParametersFactory, $jobRegistry, '/', 'prod');
    }

    function it_is_a_job_launcher()
    {
        $this->shouldHaveType('Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface');
    }
}
