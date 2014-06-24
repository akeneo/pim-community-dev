<?php

namespace spec\Pim\Bundle\ImportExportBundle\Factory;

use PhpSpec\ObjectBehavior;

class JobInstanceFactorySpec extends ObjectBehavior
{
    const TESTED_CLASS = 'Akeneo\Bundle\BatchBundle\Entity\JobInstance';

    function let()
    {
        $this->beConstructedWith(self::TESTED_CLASS);
    }

    function it_should_create_an_instance_of_my_object()
    {
        $this->createJobInstance()->shouldReturnAnInstanceOf(self::TESTED_CLASS);
    }

    function it_should_create_an_instance_of_my_object_with_some_values()
    {
        $jobInstance = $this->createJobInstance('foo', 'bar', 'baz');
        $jobInstance->shouldBeAnInstanceOf(self::TESTED_CLASS);
        $jobInstance->getConnector()->shouldReturn('foo');
        $jobInstance->getType()->shouldReturn('bar');
        $jobInstance->getAlias()->shouldReturn('baz');
    }
}
