<?php

namespace spec\Akeneo\Component\Batch\Event;

use Akeneo\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;

class JobExecutionEventSpec extends ObjectBehavior
{
    function let(JobExecution $jobExecution)
    {
        $this->beConstructedWith($jobExecution);
    }

    function it_provides_the_job_execution($jobExecution)
    {
        $this->getJobExecution()->shouldReturn($jobExecution);
    }
}
