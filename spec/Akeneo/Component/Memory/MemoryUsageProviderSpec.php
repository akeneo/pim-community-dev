<?php

namespace spec\Akeneo\Component\Memory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MemoryUsageProviderSpec extends ObjectBehavior
{
    function it_provides_memory_limit()
    {
        $this->getLimit()->shouldBeInt();
    }

    function it_provides_memory_usage()
    {
        $this->getUsage()->shouldBeInt();
    }

    function it_provides_peak_memory_usage()
    {
        $this->getPeakUsage()->shouldBeInt();
    }
}
