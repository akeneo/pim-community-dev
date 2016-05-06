<?php

namespace spec\Akeneo\Component\Batch\Item;

use PhpSpec\ObjectBehavior;

class InvalidItemExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            'Tango is down, I repeat...',
            ['foo' => 'fighter']
        );
    }

    function it_provides_the_message()
    {
        $this->getMessage()->shouldReturn('Tango is down, I repeat...');
    }

    function it_provides_the_invalid_item()
    {
        $this->getItem()->shouldReturn(['foo' => 'fighter']);
    }
}
