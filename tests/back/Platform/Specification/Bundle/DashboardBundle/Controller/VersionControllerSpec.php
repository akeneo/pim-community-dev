<?php
declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\DashboardBundle\Controller;


use Akeneo\Platform\Bundle\DashboardBundle\Controller\VersionController;
use Akeneo\Platform\VersionProviderInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\JsonResponse;

final class VersionControllerSpec extends ObjectBehavior
{
    function let(VersionProviderInterface $versionProvider, ConfigManager $configManager)
    {
        $this->beConstructedWith($versionProvider, $configManager, 'https://update.akeneo.com/');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VersionController::class);
    }

    function it_provides_serenity_version_data($versionProvider, $configManager)
    {
        $versionProvider->getFullVersion()->willReturn('Serenity 20210315010101 Daisy');
        $versionProvider->getVersion()->willReturn('20210315010101');
        $versionProvider->getEdition()->willReturn('Serenity');
        $versionProvider->getMinorVersion()->willReturn('20210315010101');
        $configManager->get('pim_analytics.version_update')->willReturn(true);

        $response = $this->__invoke();
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getContent()->shouldReturn(json_encode(
            [
                'version' => 'Serenity 20210315010101 Daisy',
                'is_last_patch_displayed' => false,
                'analytics_url' => 'https://update.akeneo.com/Serenity-20210315010101.json',
                'is_analytics_wanted' => true
            ]
        ));
    }

    function it_provides_growth_version_data($versionProvider, $configManager)
    {
        $versionProvider->getFullVersion()->willReturn('GE 20210315010101 Daisy');
        $versionProvider->getVersion()->willReturn('20210315010101');
        $versionProvider->getEdition()->willReturn('GE');
        $versionProvider->getMinorVersion()->willReturn('20210315010101');
        $configManager->get('pim_analytics.version_update')->willReturn(true);

        $response = $this->__invoke();
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getContent()->shouldReturn(json_encode(
            [
                'version' => 'GE 20210315010101 Daisy',
                'is_last_patch_displayed' => false,
                'analytics_url' => 'https://update.akeneo.com/GE-20210315010101.json',
                'is_analytics_wanted' => true
            ]
        ));
    }

    function it_provides_general_availability_version_data($versionProvider, $configManager)
    {
        $versionProvider->getFullVersion()->willReturn('CE 6.0.1 Daisy');
        $versionProvider->getVersion()->willReturn('6.0.1');
        $versionProvider->getEdition()->willReturn('CE');
        $versionProvider->getMinorVersion()->willReturn('6.0');
        $configManager->get('pim_analytics.version_update')->willReturn(true);

        $response = $this->__invoke();
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getContent()->shouldReturn(json_encode(
            [
                'version' => 'CE 6.0.1 Daisy',
                'is_last_patch_displayed' => true,
                'analytics_url' => 'https://update.akeneo.com/CE-6.0.json',
                'is_analytics_wanted' => true
            ]
        ));
    }

    function it_tells_the_frontend_to_hide_next_patch_available($versionProvider, $configManager)
    {
        $versionProvider->getFullVersion()->willReturn('CE 6.0.1 Daisy');
        $versionProvider->getVersion()->willReturn('6.0.1');
        $versionProvider->getEdition()->willReturn('CE');
        $versionProvider->getMinorVersion()->willReturn('6.0');
        $configManager->get('pim_analytics.version_update')->willReturn(false);

        $response = $this->__invoke();
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getContent()->shouldReturn(json_encode(
            [
                'version' => 'CE 6.0.1 Daisy',
                'is_last_patch_displayed' => false,
                'analytics_url' => 'https://update.akeneo.com/CE-6.0.json',
                'is_analytics_wanted' => true
            ]
        ));
    }

    function it_provides_master_version_data($versionProvider, $configManager)
    {
        $versionProvider->getFullVersion()->willReturn('CE master Daisy');
        $versionProvider->getVersion()->willReturn('master');
        $versionProvider->getEdition()->willReturn('CE');
        $versionProvider->getMinorVersion()->willReturn('master');
        $configManager->get('pim_analytics.version_update')->willReturn(true);

        $response = $this->__invoke();
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getContent()->shouldReturn(json_encode(
            [
                'version' => 'CE master Daisy',
                'is_last_patch_displayed' => false,
                'analytics_url' => 'https://update.akeneo.com/CE-master.json',
                'is_analytics_wanted' => false
            ]
        ));
    }
}
