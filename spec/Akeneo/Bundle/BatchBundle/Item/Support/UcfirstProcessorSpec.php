<?php

namespace spec\Akeneo\Bundle\BatchBundle\Item\Support;

use PhpSpec\ObjectBehavior;

class UcfirstProcessorSpec extends ObjectBehavior
{
    function it_processes()
    {
        $this->process('item1')->shouldReturn('Item1');
    }
}