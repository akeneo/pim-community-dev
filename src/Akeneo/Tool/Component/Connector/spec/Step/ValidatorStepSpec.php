<?php

namespace spec\Akeneo\Tool\Component\Connector\Step;

use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\Item\CharsetValidator;
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
        $this->shouldHaveType('\Akeneo\Tool\Component\Connector\Step\ValidatorStep');
    }

    function it_is_a_step()
    {
        $this->shouldHaveType('\Akeneo\Tool\Component\Batch\Step\StepInterface');
        $this->shouldHaveType('\Akeneo\Tool\Component\Batch\Step\AbstractStep');
    }
}
