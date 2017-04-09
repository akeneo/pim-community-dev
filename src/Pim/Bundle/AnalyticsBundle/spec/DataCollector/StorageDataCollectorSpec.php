<?php

namespace spec\Pim\Bundle\AnalyticsBundle\DataCollector;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RequestStack;

class StorageDataCollectorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\AnalyticsBundle\DataCollector\StorageDataCollector');
        $this->shouldImplement('Akeneo\Component\Analytics\DataCollectorInterface');
    }

    function it_provides_server_and_sql_versions_when_pim_uses_orm()
    {
        $this->collect()->shouldHaveCount(2);
    }
}
