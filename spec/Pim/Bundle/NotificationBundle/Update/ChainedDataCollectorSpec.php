<?php

namespace spec\Pim\Bundle\NotificationBundle\Update;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\NotificationBundle\Update\DataCollectorInterface;
use Prophecy\Argument;

class ChainedDataCollectorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Update\ChainedDataCollector');
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Update\DataCollectorRegistryInterface');
        $this->shouldHaveType('Pim\Bundle\NotificationBundle\Update\DataCollectorInterface');
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
