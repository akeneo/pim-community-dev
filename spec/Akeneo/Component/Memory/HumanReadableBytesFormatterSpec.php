<?php

namespace spec\Akeneo\Component\Memory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HumanReadableBytesFormatterSpec extends ObjectBehavior
{
    function it_formats_unlimited_bytes()
    {
        $this->format(-1, 2)->shouldReturn('Unlimited');
    }

    function it_formats_bytes_to_KB()
    {
        $this->format(20000, 2)->shouldReturn('19.53kB');
    }

    function it_formats_bytes_to_KB_with_4_digits()
    {
        $this->format(20000, 4)->shouldReturn('19.5312kB');
    }

    function it_formats_bytes_to_MB()
    {
        $this->format(200000000, 2)->shouldReturn('190.73MB');
    }

    function it_formats_bytes_to_GB()
    {
        $this->format(20000000000, 2)->shouldReturn('18.63GB');
    }
}
