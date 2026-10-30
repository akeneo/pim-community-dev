<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Controller\ExternalApi;

use Akeneo\Platform\Bundle\PimVersionBundle\VersionProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GetSystemInformationController
{
    public function __construct(
        private VersionProviderInterface $versionProvider,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $response = [
            'version' => strtolower($this->versionProvider->getVersion()),
            'edition' => $this->versionProvider->getEdition(),
        ];

        return new JsonResponse($response);
    }
}
