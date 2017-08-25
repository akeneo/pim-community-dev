<?php

namespace spec\Akeneo\Bundle\BatchBundle\Launcher;

use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use PhpSpec\ObjectBehavior;

class SimpleJobLauncherSpec extends ObjectBehavior
{
    function let(
        JobRepositoryInterface $jobRepository,
        JobParametersFactory $jobParametersFactory,
        JobRegistry $jobRegistry,
        JobParametersValidator $jobParametersValidator
    ) {
        $this->beConstructedWith($jobRepository, $jobParametersFactory, $jobRegistry, $jobParametersValidator, '/', 'prod', '/logs');
    }

    function it_is_a_job_launcher()
    {
        $this->shouldHaveType('Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface');
    }
}
