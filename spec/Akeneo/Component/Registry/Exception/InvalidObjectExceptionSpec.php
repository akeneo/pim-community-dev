<?php

namespace spec\Akeneo\Component\Registry\Exception;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class InvalidObjectExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('\My\Type');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Registry\Exception\InvalidObjectException');
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType('\InvalidArgumentException');
    }
}
