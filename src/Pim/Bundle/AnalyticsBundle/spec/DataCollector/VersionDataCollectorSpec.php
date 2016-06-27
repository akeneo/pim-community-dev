<?php

namespace spec\Pim\Bundle\AnalyticsBundle\DataCollector;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\VersionProviderInterface;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ServerBag;

class VersionDataCollectorSpec extends ObjectBehavior
{
    function let(RequestStack $requestStack, VersionProviderInterface $versionProvider)
    {
        $this->beConstructedWith($requestStack, $versionProvider, 'prod', '2015-09-16T10:10:32+02:00');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\AnalyticsBundle\DataCollector\VersionDataCollector');
        $this->shouldImplement('Akeneo\Component\Analytics\DataCollectorInterface');
    }

    function it_collects_pim_version_edition_and_storage_driver(
        $requestStack,
        $versionProvider,
        Request $request,
        ServerBag $serverBag
    ) {
        $versionProvider->getPatch()->willReturn('1.4.0');
        $versionProvider->getEdition()->willReturn('CE');
        $requestStack->getCurrentRequest()->willReturn($request);
        $request->server = $serverBag;
        $serverBag->get('SERVER_SOFTWARE')->willReturn('Apache/2.4.12 (Debian)');

        $this->collect()->shouldReturn(
            [
                'pim_edition'        => 'CE',
                'pim_version'        => '1.4.0',
                'pim_environment'    => 'prod',
                'pim_install_time'   => '2015-09-16T10:10:32+02:00',
                'server_version'     => 'Apache/2.4.12 (Debian)',
            ]
        );
    }

    function it_does_not_provides_server_version_of_pim_host_if_request_is_null(
        $requestStack,
        $versionProvider,
        ServerBag $serverBag
    ) {
        $versionProvider->getPatch()->willReturn('1.4.0');
        $versionProvider->getEdition()->willReturn('CE');
        $requestStack->getCurrentRequest()->willReturn(null);

        $serverBag->get(Argument::type('string'))->shouldNotBeCalled();

        $this->collect()->shouldReturn(
            [
                'pim_edition'      => 'CE',
                'pim_version'      => '1.4.0',
                'pim_environment'  => 'prod',
                'pim_install_time' => '2015-09-16T10:10:32+02:00',
                'server_version'     => '',
            ]
        );
    }
}
