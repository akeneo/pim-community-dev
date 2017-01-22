<?php

namespace spec\Pim\Component\Structuring\Value;

use Pim\Component\Structuring\Value;
use Pim\Component\Structuring\ValueInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ScalarValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('My string');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Value::class);
    }

    function it_is_a_value()
    {
        $this->shouldImplement(ValueInterface::class);
    }

    function it_has_data()
    {
        $this->getData()->shouldReturn('My string');
    }
}
