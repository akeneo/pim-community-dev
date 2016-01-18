<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Step;

use Akeneo\Bundle\BatchBundle\Job\JobRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Validator\Step\CharsetValidator;

class ValidatorStepSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('aName');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Step\ValidatorStep');
    }

    function it_is_a_step()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Step\StepInterface');
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Step\AbstractStep');
    }

    function it_is_configurable(
        JobRepositoryInterface $jobRepository,
        CharsetValidator $charsetValidator
    ) {
        $this->setJobRepository($jobRepository);
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
