<?php

namespace spec\Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Job\RefreshProjectCompleteness;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Job\RefreshProjectCompleteness\RefreshProjectCompletenessJobParameter;
use Symfony\Component\Validator\Constraints\Collection;

class RefreshProjectCompletenessJobParameterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('process_attribute_completeness');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RefreshProjectCompletenessJobParameter::class);
    }

    function it_is_default_value_provider()
    {
        $this->shouldImplement(DefaultValuesProviderInterface::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintCollectionProviderInterface::class);
    }

    function it_provides_constraints_to_the_job()
    {
        $this->getConstraintCollection()->shouldHaveType(Collection::class);
    }

    function it_specifies_supported_job(JobInterface $job)
    {
        $job->getName()->willReturn('process_attribute_completeness');
        $this->supports($job)->shouldReturn(true);

        $job->getName()->willReturn('other_job');
        $this->supports($job)->shouldReturn(false);
    }
}
