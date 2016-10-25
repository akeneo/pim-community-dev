<?php

namespace spec\Akeneo\ActivityManager\Component\Job;

use Akeneo\ActivityManager\Component\Job\ProjectCalculationJobParameters;
use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints\Collection;

class ProjectCalculationJobParametersSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectCalculationJobParameters::class);
    }

    function it_is_default_value_provider()
    {
        $this->shouldImplement(DefaultValuesProviderInterface::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintCollectionProviderInterface::class);
    }

    function it_provides_default_values_to_the_job()
    {
        $this->getDefaultValues()->shouldReturn([]);
    }

    function it_provides_constraints_to_the_job()
    {
        $this->getConstraintCollection()->shouldHaveType(Collection::class);
    }

    function it_specifies_supported_job(JobInterface $job)
    {
        $job->getName()->willReturn(ProjectCalculationJobParameters::JOB_NAME);
        $this->supports($job)->shouldReturn(true);

        $job->getName()->willReturn('other_job');
        $this->supports($job)->shouldReturn(false);
    }
}
