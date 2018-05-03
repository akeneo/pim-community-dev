<?php

namespace spec\Akeneo\Tool\Component\StorageUtils\Factory;

use PhpSpec\ObjectBehavior;

class SimpleFactorySpec extends ObjectBehavior
{
    const MY_CLASS = 'stdClass';

    function let()
    {
        $this->beConstructedWith(self::MY_CLASS);
    }

    function it_creates_an_object()
    {
        $this->create()->shouldReturnAnInstanceOf(self::MY_CLASS);
    }
}
