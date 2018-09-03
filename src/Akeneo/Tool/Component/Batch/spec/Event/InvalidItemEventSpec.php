<?php

namespace spec\Akeneo\Tool\Component\Batch\Event;

use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use PhpSpec\ObjectBehavior;

class InvalidItemEventSpec extends ObjectBehavior
{
    function let(InvalidItemInterface $invalidItem)
    {
        $this->beConstructedWith(
            $invalidItem,
            'Foo\\Bar\\Baz',
            'No special reason %param%.',
            ['%param%' => 'Item1']
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

    function it_provides_invalid_item($invalidItem)
    {
        $this->getItem()->shouldReturn($invalidItem);
    }
}
