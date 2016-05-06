<?php

namespace spec\Pim\Bundle\AnalyticsBundle\Provider;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StorageVersionProviderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\AnalyticsBundle\Provider\StorageVersionProvider');
    }

    function it_provides_storage_version_when_pim_uses_orm()
    {
        $this->beConstructedWith(null, null);

        $this->provide()->shouldHaveCount(1);
    }
}
