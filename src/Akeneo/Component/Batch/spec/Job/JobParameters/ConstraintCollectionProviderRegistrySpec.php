<?php

namespace spec\Akeneo\Component\Batch\Job\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Component\Batch\Job\JobParameters\NonExistingServiceException;
use PhpSpec\ObjectBehavior;

class ConstraintCollectionProviderRegistrySpec extends ObjectBehavior
{
    function it_gets_the_registered_provider_for_a_job(ConstraintCollectionProviderInterface $provider, JobInterface $job)
    {
        $this->register($provider, $job);
        $provider->supports($job)->willReturn(true);
        $this->get($job)->shouldReturn($provider);
    }

    function it_throws_an_exception_when_there_is_no_registered_provider(JobInterface $job)
    {
        $job->getName()->willReturn('myname');
        $this->shouldThrow(
            new NonExistingServiceException(
                'No contraint collection provider has been defined for the Job "myname"'
            )
        )->during(
            'get',
            [$job]
        );
    }
}
