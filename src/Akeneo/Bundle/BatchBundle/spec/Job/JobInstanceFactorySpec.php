<?php

namespace spec\Akeneo\Bundle\BatchBundle\Job;

use PhpSpec\ObjectBehavior;

class JobInstanceFactorySpec extends ObjectBehavior
{
    const TESTED_CLASS = 'Akeneo\Component\Batch\Model\JobInstance';

    function let()
    {
        $this->beConstructedWith(self::TESTED_CLASS);
    }

    function it_creates_job_instances()
    {
        $this->createJobInstance()->shouldReturnAnInstanceOf(self::TESTED_CLASS);
    }

    function it_creates_job_instances_with_defined_type()
    {
        $jobInstance = $this->createJobInstance('foo');
        $jobInstance->shouldBeAnInstanceOf(self::TESTED_CLASS);
        $jobInstance->getType()->shouldReturn('foo');
        $jobInstance->getJobName()->shouldReturn(null);
        $jobInstance->getConnector()->shouldReturn(null);
    }
}
