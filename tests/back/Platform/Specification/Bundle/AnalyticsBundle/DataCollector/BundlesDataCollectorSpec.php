<?php

namespace Specification\Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector\BundlesDataCollector;

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
        $this->shouldHaveType(BundlesDataCollector::class);
        $this->shouldHaveType(DataCollectorInterface::class);
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
