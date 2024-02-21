<?php

namespace spec\Akeneo\Tool\Component\Connector\Processor;

use PhpSpec\ObjectBehavior;

class DummyItemProcessorSpec extends ObjectBehavior
{
    function it_does_nothing_when_process_items()
    {
        $this->process('foo')->shouldReturn(null);
    }
}
