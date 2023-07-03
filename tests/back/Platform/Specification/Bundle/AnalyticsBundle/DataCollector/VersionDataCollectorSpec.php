<?php

namespace Specification\Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector\VersionDataCollector;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Platform\Bundle\InstallerBundle\InstallStatusManager\InstallStatusManager;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ServerBag;

class VersionDataCollectorSpec extends ObjectBehavior
{
    function let(
        RequestStack $requestStack,
        VersionProviderInterface $versionProvider,
        InstallStatusManager $installStatusManager,
        FeatureFlags $featureFlags,
    ) {
        $this->beConstructedWith($requestStack, $versionProvider, $installStatusManager, 'prod', $featureFlags);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(VersionDataCollector::class);
        $this->shouldImplement(DataCollectorInterface::class);
    }

    public function it_collects_pim_version_edition_and_storage_driver(
        RequestStack $requestStack,
        VersionProviderInterface $versionProvider,
        InstallStatusManager $installStatusManager,
        Request $request,
        ServerBag $serverBag,
        FeatureFlags $featureFlags,
    ) {
        $featureFlags->isEnabled('reset_pim')->willReturn(true);
        $versionProvider->getPatch()->willReturn('1.4.0');
        $versionProvider->getEdition()->willReturn('CE');
        $requestStack->getCurrentRequest()->willReturn($request);
        $installStatusManager->getPimInstallDateTime()->willReturn(new \DateTime('2015-09-16T10:10:32+02:00'));
        $installStatusManager->getPimResetData()->willReturn(['reset_events' => [['time' => '2015-09-17T10:10:32+02:00']]]);
        $request->server = $serverBag;
        $serverBag->get('SERVER_SOFTWARE')->willReturn('Apache/2.4.12 (Debian)');

        $this->collect()->shouldReturn(
            [
                'pim_edition'        => 'CE',
                'pim_version'        => '1.4.0',
                'pim_environment'    => 'prod',
                'pim_install_time'   => (new \DateTime('2015-09-16T10:10:32+02:00'))->format(\DateTime::ATOM),
                'server_version'     => 'Apache/2.4.12 (Debian)',
                'reset_event_count'  => 1,
                'last_reset_time'    => '2015-09-17T10:10:32+02:00',
            ]
        );
    }

    public function it_does_not_provides_server_version_of_pim_host_if_request_is_null(
        RequestStack $requestStack,
        VersionProviderInterface $versionProvider,
        InstallStatusManager $installStatusManager,
        ServerBag $serverBag,
        FeatureFlags $featureFlags,
    ) {
        $featureFlags->isEnabled('reset_pim')->willReturn(false);
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
                'pim_install_time' => (new \DateTime('2015-09-16T10:10:32+02:00'))->format(\DateTime::ATOM),
                'server_version'   => '',
            ]
        );
    }
}
