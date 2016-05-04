<?php

namespace spec\Pim\Bundle\AnalyticsBundle\DataCollector;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\AnalyticsBundle\Provider\ServerVersionProvider;
use Pim\Bundle\AnalyticsBundle\Provider\StorageVersionProvider;
use Prophecy\Argument;

class AdvancedVersionDataCollectorSpec extends ObjectBehavior
{
    function let(StorageVersionProvider $storageVersionProvider, ServerVersionProvider $serverVersionProvider)
    {
        $this->beConstructedWith($storageVersionProvider, $serverVersionProvider);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\AnalyticsBundle\DataCollector\AdvancedVersionDataCollector');
        $this->shouldHaveType('Akeneo\Component\Analytics\DataCollectorInterface');
    }

    function it_collects_pim_storage_versions_and_server_versions($storageVersionProvider, $serverVersionProvider)
    {
        $storageVersionProvider->provide()->willReturn(['mysql_version' => '5.6.30', 'mongodb_version' => '2.4.14']);
        $serverVersionProvider->provide()->willReturn(['server_version' => 'Apache/2.4.18 (Debian)']);

        $this->collect()->shouldReturn(
            [
                'mysql_version'   => '5.6.30',
                'mongodb_version' => '2.4.14',
                'server_version'  => 'Apache/2.4.18 (Debian)',
            ]
        );
    }
}
