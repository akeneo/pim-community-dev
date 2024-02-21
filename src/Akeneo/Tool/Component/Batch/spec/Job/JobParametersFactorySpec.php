<?php

namespace spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use PhpSpec\ObjectBehavior;

class JobParametersFactorySpec extends ObjectBehavior
{
    const INSTANCE_CLASS = JobParameters::class;

    function let(DefaultValuesProviderRegistry $registry)
    {
        $this->beConstructedWith($registry, self::INSTANCE_CLASS);
    }

    function it_creates_a_job_parameters_with_default_values(
        $registry,
        DefaultValuesProviderInterface $provider,
        JobInterface $job
    ) {
        $job->getName()->willReturn('foo');
        $registry->get($job)->willReturn($provider);
        $provider->getDefaultValues()->willReturn(['my_default_field' => 'my default value']);

        $jobParameters = $this->create($job, ['my_defined_field' => 'my defined value']);

        $jobParameters->shouldReturnAnInstanceOf(JobParameters::class);
        $jobParameters->all()->shouldBe(
            [
                'my_default_field' => 'my default value',
                'my_defined_field' => 'my defined value',
            ]
        );
    }

    function it_creates_a_job_parameters_from_raw_parameters_of_a_job_execution(JobExecution $jobExecution)
    {
        $jobExecution->getRawParameters()->willreturn(['foo' => 'baz']);
        $jobParameters = $this->createFromRawParameters($jobExecution);

        $jobParameters->shouldReturnAnInstanceOf(JobParameters::class);
        $jobParameters->all()->shouldBe(['foo' => 'baz']);
    }
}
