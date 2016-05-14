<?php

namespace spec\Akeneo\Component\Batch\Job\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PhpSpec\ObjectBehavior;

class ConstraintCollectionProviderRegistrySpec extends ObjectBehavior
{
    function it_gets_the_registered_provider_for_a_job(ConstraintCollectionProviderInterface $provider, JobInterface $job)
    {
        $this->register($provider, $job);
        $provider->supports($job)->willReturn(true);
        $this->get($job)->shouldReturn($provider);
    }

    function it_gets_a_default_provider_for_a_job_when_there_is_no_registered_provider(JobInterface $job)
    {
        $defaultProviderClass = 'Akeneo\Component\Batch\Job\JobParameters\DefaultConstraintCollectionProvider';
        $this->get($job)->shouldReturnAnInstanceOf($defaultProviderClass);
    }
}
