<?php

namespace spec\Pim\Bundle\AnalyticsBundle\DataCollector;

use PhpSpec\ObjectBehavior;

class BundlesDataCollectorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            [
                'Pim\Bundle\A',
                'Pim\Bundle\C',
                'Pim\Bundle\B'
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\AnalyticsBundle\DataCollector\BundlesDataCollector');
        $this->shouldHaveType('Akeneo\Component\Analytics\DataCollectorInterface');
    }

    function it_collects_registered_bundles()
    {
        $this->collect()->shouldReturn(
            [
                'registered_bundles' => [
                    'Pim\Bundle\A',
                    'Pim\Bundle\B',
                    'Pim\Bundle\C'
                ]
            ]
        );
    }
}
