<?php

namespace spec\Pim\Bundle\AnalyticsBundle\UrlGenerator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\VersionProviderInterface;
use Akeneo\Component\Analytics\DataCollectorInterface;
use Prophecy\Argument;

class UpdateUrlGeneratorSpec extends ObjectBehavior
{
    function let(
        \Akeneo\Component\Analytics\DataCollectorInterface $dataCollector,
        VersionProviderInterface $versionProvider
    ) {
        $this->beConstructedWith($dataCollector, $versionProvider, 'https://updates.akeneo.com');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\AnalyticsBundle\UrlGenerator\UpdateUrlGeneratorInterface');
    }

    function it_generates_update_url($dataCollector, $versionProvider)
    {
        $dataCollector->collect()->willReturn(['pim_version' => '1.4.2', 'php_version' => '5.5.9-1ubuntu4.11']);
        $versionProvider->getEdition()->willReturn('CE');
        $versionProvider->getMinor()->willReturn('1.4');
        $expectedUrl = 'https://updates.akeneo.com/CE-1.4?pim_version=1.4.2&php_version=5.5.9-1ubuntu4.11';
        $this->generateAvailablePatchsUrl()->shouldReturn($expectedUrl);
    }
}
