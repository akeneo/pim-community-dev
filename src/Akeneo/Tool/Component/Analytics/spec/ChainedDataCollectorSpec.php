<?php

namespace spec\Akeneo\Tool\Component\Analytics;

use Akeneo\Tool\Component\Analytics\ChainedDataCollector;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use PhpSpec\ObjectBehavior;

class ChainedDataCollectorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ChainedDataCollector::class);
    }

    function it_aggregates_data_from_registered_collectors(
        DataCollectorInterface $collectorOne,
        DataCollectorInterface $collectorTwo,
        DataCollectorInterface $collectorThree,
        DataCollectorInterface $defaultTypeCollector
    ) {
        $collectorOne->collect()->willReturn(['data_one' => 'one']);
        $collectorTwo->collect()->willReturn(['data_two' => 'two', 'data_three' => 'three']);
        $collectorThree->collect()->willReturn(['data_four' => 'four']);
        $defaultTypeCollector->collect()->willReturn(['data_five' => 'five']);

        $this->addCollector($collectorOne, 'type1');
        $this->addCollector($collectorTwo, 'type2');
        $this->addCollector($collectorThree, 'type2');
        $this->addCollector($defaultTypeCollector);

        $this->collect('type1')->shouldReturn(['data_one' => 'one']);
        $this->collect('type2')->shouldReturn(['data_two' => 'two', 'data_three' => 'three', 'data_four' => 'four']);
        $this->collect(ChainedDataCollector::DEFAULT_COLLECTOR_TYPE)->shouldReturn(['data_five' => 'five']);
    }
}
