<?php

namespace spec\Pim\Bundle\ImportExportBundle\Factory;

use PhpSpec\ObjectBehavior;

class JobInstanceFactorySpec extends ObjectBehavior
{
    const TESTED_CLASS = 'spec\Pim\Bundle\ImportExportBundle\Factory\MyObjectClass';

    function let()
    {
        $this->beConstructedWith(self::TESTED_CLASS);
    }

    function it_should_create_an_instance_of_my_object()
    {
        $this->createJobInstance()->shouldReturnAnInstanceOf(self::TESTED_CLASS);
    }
}

class MyObjectClass
{
}
