<?php

namespace Specification\Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector\VersionDataCollector;
use Akeneo\Platform\VersionProviderInterface;
use Akeneo\Platform\Bundle\InstallerBundle\InstallStatusManager\InstallStatusManager;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ServerBag;

class VersionDataCollectorSpec extends ObjectBehavior
{
    function let(
        RequestStack $requestStack,
        VersionProviderInterface $versionProvider,
        InstallStatusManager $installStatusManager
    ) {
        $this->beConstructedWith($requestStack, $versionProvider, $installStatusManager, 'prod');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(VersionDataCollector::class);
        $this->shouldImplement(DataCollectorInterface::class);
    }

    public function it_collects_pim_version_edition_and_storage_driver
    (
        $requestStack,
        $versionProvider,
        $installStatusManager,
        Request $request,
        ServerBag $serverBag
    ) {
        $versionProvider->getPatch()->willReturn('1.4.0');
        $versionProvider->getEdition()->willReturn('CE');
        $requestStack->getCurrentRequest()->willReturn($request);
        $installStatusManager->getPimInstallDateTime()->willReturn(new \DateTime('2015-09-16T10:10:32+02:00'));
        $request->server = $serverBag;
        $serverBag->get('SERVER_SOFTWARE')->willReturn('Apache/2.4.12 (Debian)');

        $this->collect()->shouldReturn(
            [
                'pim_edition'        => 'CE',
                'pim_version'        => '1.4.0',
                'pim_environment'    => 'prod',
                'pim_install_time'   => (new \DateTime('2015-09-16T10:10:32+02:00'))->format(\DateTime::ISO8601),
                'server_version'     => 'Apache/2.4.12 (Debian)',
            ]
        );
    }

    public function it_does_not_provides_server_version_of_pim_host_if_request_is_null(
        $requestStack,
        $versionProvider,
        $installStatusManager,
        ServerBag $serverBag
    ) {
        $versionProvider->getPatch()->willReturn('1.4.0');
        $versionProvider->getEdition()->willReturn('CE');
        $requestStack->getCurrentRequest()->willReturn(null);
        $installStatusManager->getPimInstallDateTime()->willReturn(new \DateTime('2015-09-16T10:10:32+02:00'));

        $serverBag->get(Argument::type('string'))->shouldNotBeCalled();

        $this->collect()->shouldReturn(
            [
                'pim_edition'      => 'CE',
                'pim_version'      => '1.4.0',
                'pim_environment'  => 'prod',
                'pim_install_time' => (new \DateTime('2015-09-16T10:10:32+02:00'))->format(\DateTime::ISO8601),
                'server_version'     => '',
            ]
        );
    }
}
