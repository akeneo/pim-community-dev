<?php

namespace spec\Pim\Component\Connector\Step;

use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Item\CharsetValidator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ValidatorStepSpec extends ObjectBehavior
{
    function let(
        EventDispatcherInterface $dispatcher,
        JobRepositoryInterface $jobRepository,
        CharsetValidator $validator
    ) {
        $this->beConstructedWith('aName', $dispatcher, $jobRepository, $validator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Component\Connector\Step\ValidatorStep');
    }

    function it_is_a_step()
    {
        $this->shouldHaveType('\Akeneo\Component\Batch\Step\StepInterface');
        $this->shouldHaveType('\Akeneo\Component\Batch\Step\AbstractStep');
    }
}
