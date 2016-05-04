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

    function it_provides_storage_version_when_pim_use_orm()
    {
        $this->beConstructedWith(null, null);

        $this->provide()->shouldHaveCount(1);
    }

    function it_provides_storage_versions_when_pim_use_odm()
    {
        $this->beConstructedWith('mongodb://localhost:27017', 'pim_ce_dev');

        $this->provide()->shouldHaveCount(2);
    }
}
