<?php

namespace spec\Akeneo\Bundle\BatchBundle\Launcher;

use Akeneo\Bundle\BatchBundle\Job\JobRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SimpleJobLauncherSpec extends ObjectBehavior
{
    function let(JobRepositoryInterface $jobRepository) {
        $this->beConstructedWith($jobRepository, '/', 'prod');
    }

    function it_is_a_job_launcher()
    {
        $this->shouldHaveType('Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface');
    }
}
