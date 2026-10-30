<?php
declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\DashboardBundle\Controller;


use Akeneo\Platform\Bundle\DashboardBundle\Controller\VersionController;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\JsonResponse;

final class VersionControllerSpec extends ObjectBehavior
{
    function let(VersionProviderInterface $versionProvider)
    {
        $this->beConstructedWith($versionProvider, 'https://update.akeneo.com/');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(VersionController::class);
    }

    function it_provides_version_data($versionProvider)
    {
        $versionProvider->getFullVersion()->willReturn('CE 2026.5 Sahara Hare');
        $versionProvider->getVersion()->willReturn('2026.5');
        $versionProvider->getEdition()->willReturn('CE');

        $response = $this->__invoke();
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getContent()->shouldReturn(json_encode(
            [
                'version' => 'CE 2026.5 Sahara Hare',
                'analytics_url' => 'https://update.akeneo.com/CE-2026.5.json',
                'is_analytics_wanted' => true
            ]
        ));
    }

    function it_provides_master_version_data($versionProvider)
    {
        $versionProvider->getFullVersion()->willReturn('CE master Sahara Hare');
        $versionProvider->getVersion()->willReturn('master');
        $versionProvider->getEdition()->willReturn('CE');

        $response = $this->__invoke();
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getContent()->shouldReturn(json_encode(
            [
                'version' => 'CE master Sahara Hare',
                'analytics_url' => 'https://update.akeneo.com/CE-master.json',
                'is_analytics_wanted' => false
            ]
        ));
    }
}
