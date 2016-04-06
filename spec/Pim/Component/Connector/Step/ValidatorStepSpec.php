<?php

namespace spec\Pim\Component\Connector\Step;

use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Item\CharsetValidator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ValidatorStepSpec extends ObjectBehavior
{
    function let(
        EventDispatcherInterface $dispatcher,
        JobRepositoryInterface $jobRepository
    ) {
        $this->beConstructedWith('aName', $jobRepository, $dispatcher);
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

    function it_is_configurable(
        $jobRepository,
        CharsetValidator $charsetValidator
    ) {
        $this->setCharsetValidator($charsetValidator);

        $this->getJobRepository()->shouldReturn($jobRepository);
        $this->getCharsetValidator()->shouldReturn($charsetValidator);
    }

    function it_provides_configuration(CharsetValidator $charsetValidator)
    {
        $charsetValidator->getConfiguration()->willReturn(['withHeader' => true, 'enclosure' => ';']);

        $this->setCharsetValidator($charsetValidator);
        $this->getConfiguration()->shouldReturn(['withHeader' => true, 'enclosure' => ';']);
    }

    function it_sets_a_configuration(CharsetValidator $charsetValidator)
    {
        $charsetValidator->setConfiguration(['withHeader' => true, 'enclosure' => ';'])->shouldBeCalled();

        $this->setCharsetValidator($charsetValidator);
        $this->setConfiguration(['withHeader' => true, 'enclosure' => ';']);
    }
}
