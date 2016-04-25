<?php

namespace spec\Akeneo\Component\Registry\Exception;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ExistingObjectExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('alias');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Registry\Exception\ExistingObjectException');
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType('\InvalidArgumentException');
    }
}
