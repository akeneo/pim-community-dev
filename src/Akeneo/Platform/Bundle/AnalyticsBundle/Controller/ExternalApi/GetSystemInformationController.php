<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Controller\ExternalApi;

use Akeneo\Platform\Bundle\PimVersionBundle\Version\GrowthVersion;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GetSystemInformationController
{
    public function __construct(
        private VersionProviderInterface $versionProvider,
        private GrowthVersion $growthVersion,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $edition = $this->versionProvider->getEdition();
        $response = [
            'version' => strtolower($this->versionProvider->getVersion()),
            'edition' => $edition === $this->growthVersion->editionName() ? 'GE' : $edition,
        ];

        return new JsonResponse($response);
    }
}
