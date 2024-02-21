<?php

namespace spec\Akeneo\Tool\Component\Connector\Reader;

use PhpSpec\ObjectBehavior;

class DummyItemReaderSpec extends ObjectBehavior
{
    function it_does_nothing_when_read_items()
    {
        $this->read()->shouldReturn(null);
    }
}
