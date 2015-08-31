<?php

namespace spec\Akeneo\Component\Analytics;

use Akeneo\Component\Analytics\DataCollectorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChainedDataCollectorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Component\Analytics\ChainedDataCollector');
        $this->shouldHaveType('Akeneo\Component\Analytics\DataCollectorRegistryInterface');
        $this->shouldHaveType('Akeneo\Component\Analytics\DataCollectorInterface');
    }

    function it_collects()
    {
        $this->collect()->shouldReturn([]);
    }

    function it_aggregates_data_from_registered_collectors(
        DataCollectorInterface $collectorOne,
        DataCollectorInterface $collectorTwo
    ) {
        $collectorOne->collect()->willReturn(['data_one' => 'one']);
        $collectorTwo->collect()->willReturn(['data_two' => 'two', 'data_three' => 'three']);

        $this->register($collectorOne);
        $this->register($collectorTwo);
        $this->collect()->shouldReturn(['data_one' => 'one', 'data_two' => 'two', 'data_three' => 'three']);
    }
}
