<?php

namespace Specification\Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector\OSDataCollector;

class OSDataCollectorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(OSDataCollector::class);
        $this->shouldHaveType(DataCollectorInterface::class);
    }

    function it_collects_php_version_and_os_version()
    {
        $this->collect()->shouldHaveCount(3);
    }
}
