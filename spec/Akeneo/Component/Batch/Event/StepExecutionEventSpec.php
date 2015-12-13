<?php

namespace spec\Akeneo\Component\Batch\Event;

use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;

class StepExecutionEventSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution)
    {
        $this->beConstructedWith($stepExecution);
    }

    function it_provides_the_step_execution($stepExecution)
    {
        $this->getStepExecution()->shouldReturn($stepExecution);
    }
}
