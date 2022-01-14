<?php

namespace Akeneo\Platform\Syndication\Infrastructure\Controller\ExternalApi\PlatformConfiguration;

use Akeneo\Platform\Syndication\Domain\Query\PlatformConfiguration\FindPlatformConfigurationQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetCatalogsAction
{
    private FindPlatformConfigurationQueryInterface $findPlatformConfigurationQuery;

    public function __construct(
        FindPlatformConfigurationQueryInterface $findPlatformConfigurationQuery
    ) {
        $this->findPlatformConfigurationQuery = $findPlatformConfigurationQuery;
    }

    public function listAction(string $platformConfigurationCode): JsonResponse
    {
        $platformConfiguration = $this->findPlatformConfigurationQuery->execute($platformConfigurationCode);

        return new JsonResponse($platformConfiguration->normalizeForExternalApi());
    }
}
