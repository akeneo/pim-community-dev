<?php

namespace spec\Akeneo\Bundle\BatchBundle\Job;

use PhpSpec\ObjectBehavior;

class JobInstanceFactorySpec extends ObjectBehavior
{
    const TESTED_CLASS = 'Akeneo\Bundle\BatchBundle\Entity\JobInstance';

    function let()
    {
        $this->beConstructedWith(self::TESTED_CLASS);
    }

    function it_creates_job_instances()
    {
        $this->createJobInstance()->shouldReturnAnInstanceOf(self::TESTED_CLASS);
    }

    function it_creates_job_instances_with_defined_connector_type_and_alias()
    {
        $jobInstance = $this->createJobInstance('foo', 'bar', 'baz');
        $jobInstance->shouldBeAnInstanceOf(self::TESTED_CLASS);
        $jobInstance->getConnector()->shouldReturn('foo');
        $jobInstance->getType()->shouldReturn('bar');
        $jobInstance->getAlias()->shouldReturn('baz');
    }
}
