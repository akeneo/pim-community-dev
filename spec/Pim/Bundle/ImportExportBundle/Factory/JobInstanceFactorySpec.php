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

    function it_should_create_an_instance_of_my_object_with_some_values()
    {
        $jobInstance = $this->createJobInstance('foo', 'bar', 'baz');
        $jobInstance->shouldBeAnInstanceOf(self::TESTED_CLASS);
        $jobInstance->connector->shouldReturn('foo');
        $jobInstance->type->shouldReturn('bar');
        $jobInstance->alias->shouldReturn('baz');
    }
}

class MyObjectClass
{
    public $connector;
    public $type;
    public $alias;

    public function __construct($connector = null, $type = null, $alias = null)
    {
        $this->connector     = $connector;
        $this->type          = $type;
        $this->alias         = $alias;
    }
}
