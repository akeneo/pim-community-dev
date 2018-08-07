<?php

namespace spec\Akeneo\Tool\Component\Connector\Writer;

use PhpSpec\ObjectBehavior;

class DummyItemWriterSpec extends ObjectBehavior
{
    function it_does_nothing_when_writes_items()
    {
        $this->write(['foo', 'barr'])->shouldReturn(null);
    }
}
