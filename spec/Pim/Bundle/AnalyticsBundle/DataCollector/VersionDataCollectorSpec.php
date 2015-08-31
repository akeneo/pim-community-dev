<?php

namespace spec\Pim\Bundle\AnalyticsBundle\DataCollector;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\VersionProviderInterface;
use Prophecy\Argument;

class VersionDataCollectorSpec extends ObjectBehavior
{
    function let(VersionProviderInterface $versionProvider)
    {
        $this->beConstructedWith($versionProvider, 'doctrine/orm');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\AnalyticsBundle\DataCollector\VersionDataCollector');
        $this->shouldHaveType('Akeneo\Component\Analytics\DataCollectorInterface');
    }

    function it_collects_pim_version_edition_and_storage_driver($versionProvider)
    {
        $versionProvider->getPatch()->willReturn('1.4.0');
        $versionProvider->getEdition()->willReturn('CE');
        $this->collect()->shouldReturn(
            [
                'pim_edition'        => 'CE',
                'pim_version'        => '1.4.0',
                'pim_storage_driver' => 'doctrine/orm'
            ]
        );
    }
}
