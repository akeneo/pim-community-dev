<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Bundle\AnalyticsBundle\Controller\ExternalApi;

use Akeneo\Platform\Bundle\AnalyticsBundle\Controller\ExternalApi\GetSystemInformationController;
use Akeneo\Platform\Bundle\PimVersionBundle\Version\GrowthVersion;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GetSystemInformationControllerSpec extends ObjectBehavior
{
    public function it_is_a_system_information_controller(
        VersionProviderInterface $versionProvider
    ): void
    {
        $this->beConstructedWith($versionProvider, new GrowthVersion());
        $this->shouldHaveType(GetSystemInformationController::class);
    }

    public function it_provides_system_information_for_community(
        VersionProviderInterface $versionProvider,
        Request $request
    ): void
    {
        $this->beConstructedWith($versionProvider, new GrowthVersion());
        $versionProvider->getVersion()->willReturn('12345678');
        $versionProvider->getEdition()->willReturn('CE');

        $response = $this->__invoke($request);
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getContent()->shouldReturn(json_encode(
            [
                'version' => '12345678',
                'edition' => 'CE'
            ]
        ));
    }

    public function it_provides_system_information_for_enterprise(
        VersionProviderInterface $versionProvider,
        Request $request
    ): void
    {
        $this->beConstructedWith($versionProvider, new GrowthVersion());
        $versionProvider->getVersion()->willReturn('12345678');
        $versionProvider->getEdition()->willReturn('EE');

        $response = $this->__invoke($request);
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getContent()->shouldReturn(json_encode(
            [
                'version' => '12345678',
                'edition' => 'EE'
            ]
        ));
    }

    public function it_provides_system_information_for_growthedition(VersionProviderInterface $versionProvider, Request $request): void
    {
        $this->beConstructedWith($versionProvider, new GrowthVersion());
        $versionProvider->getVersion()->willReturn('12345678');
        $versionProvider->getEdition()->willReturn('Growth Edition');

        $response = $this->__invoke($request);
        $response->shouldBeAnInstanceOf(JsonResponse::class);
        $response->getContent()->shouldReturn(json_encode(
            [
                'version' => '12345678',
                'edition' => 'GE'
            ]
        ));
    }
}
