<?php

namespace spec\Pim\Component\Structuring\Value;

use Pim\Component\Structuring\Value\CollectionValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CollectionValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['value', 'value']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CollectionValue::class);
    }

    function it_has_data()
    {
        $this->getData()->shouldReturn(['value', 'value']);
    }
}
