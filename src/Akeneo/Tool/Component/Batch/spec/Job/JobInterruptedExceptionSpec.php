<?php

namespace spec\Akeneo\Component\Batch\Job;

use Akeneo\Component\Batch\Job\BatchStatus;
use PhpSpec\ObjectBehavior;

class JobInterruptedExceptionSpec extends ObjectBehavior
{
    function it_provides_the_original_status_when_built_with_this_status(BatchStatus $status)
    {
        $this->beConstructedWith(
            'my_job_interupted_exception',
            0,
            null,
            $status
        );
        $this->getStatus()->shouldReturn($status);
    }

    function it_provides_a_stopped_status_when_built_without_any_status()
    {
        $this->beConstructedWith(
            'my_job_interupted_exception',
            0,
            null
        );
        $this->getStatus()->shouldBeAnInstanceOf('Akeneo\Component\Batch\Job\BatchStatus');
        $this->getStatus()->getValue()->shouldReturn(BatchStatus::STOPPED);
    }
}
