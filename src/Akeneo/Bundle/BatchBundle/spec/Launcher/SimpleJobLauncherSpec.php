<?php

namespace spec\Akeneo\Bundle\BatchBundle\Launcher;

use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SimpleJobLauncherSpec extends ObjectBehavior
{
    function let(
        JobRepositoryInterface $jobRepository,
        JobParametersFactory $jobParametersFactory,
        ContainerInterface $container
    ) {
        $this->beConstructedWith($jobRepository, $jobParametersFactory, $container, '/', 'prod');
    }

    function it_is_a_job_launcher()
    {
        $this->shouldHaveType('Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface');
    }
}
