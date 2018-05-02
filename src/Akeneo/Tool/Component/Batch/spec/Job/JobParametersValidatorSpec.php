<?php

namespace spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderRegistry;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class JobParametersValidatorSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator, ConstraintCollectionProviderRegistry $registry)
    {
        $this->beConstructedWith($validator, $registry);
    }

    function it_validates_a_job_parameters(
        $validator,
        $registry,
        ConstraintCollectionProviderInterface $provider,
        JobInterface $job,
        JobParameters $jobParameters
    ) {
        $registry->get($job)->willReturn($provider);
        $provider->getConstraintCollection()->willReturn(['fields' => 'my constraints']);
        $jobParameters->all()->willReturn(['my params']);
        $validator
            ->validate(['my params'], ['fields' => 'my constraints'], ['MyValidationGroup', 'Default'])
            ->shouldBeCalled();

        $this->validate($job, $jobParameters, ['MyValidationGroup', 'Default']);
    }
}
