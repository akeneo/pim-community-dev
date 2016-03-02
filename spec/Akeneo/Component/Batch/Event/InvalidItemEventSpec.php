<?php

namespace spec\Akeneo\Component\Batch\Event;

use PhpSpec\ObjectBehavior;

class InvalidItemEventSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            'Foo\\Bar\\Baz',
            'No special reason %param%.',
            ['%param%' => 'Item1'],
            ['foo'     => 'baz']
        );
    }

    function it_provides_item_class()
    {
        $this->getClass()->shouldReturn('Foo\\Bar\\Baz');
    }

    function it_provides_invalidity_reason()
    {
        $this->getReason()->shouldReturn('No special reason %param%.');
    }

    function it_provides_invalidity_reason_params()
    {
        $this->getReasonParameters()->shouldReturn(['%param%' => 'Item1']);
    }

    function it_provides_invalid_item()
    {
        $this->getItem()->shouldReturn(['foo' => 'baz']);
    }
}
