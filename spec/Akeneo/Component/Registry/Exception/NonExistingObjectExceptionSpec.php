<?php

namespace spec\Akeneo\Component\Registry\Exception;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NonExistingObjectExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('alias');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Registry\Exception\NonExistingObjectException');
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType('\DomainException');
    }
}
