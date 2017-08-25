<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation\ProjectCalculationJobParameters;
use Symfony\Component\Validator\Constraints\Collection;

class ProjectCalculationJobParametersSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('project_calculation');
    }

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
        $this->getDefaultValues()->shouldReturn(['notification_user' => null]);
    }

    function it_provides_constraints_to_the_job()
    {
        $this->getConstraintCollection()->shouldHaveType(Collection::class);
    }

    function it_specifies_supported_job(JobInterface $job)
    {
        $job->getName()->willReturn('project_calculation');
        $this->supports($job)->shouldReturn(true);

        $job->getName()->willReturn('other_job');
        $this->supports($job)->shouldReturn(false);
    }
}
