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
        try {
            $platformConfiguration = $this->findPlatformConfigurationQuery->execute($platformConfigurationCode);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 404);
        }

        return new JsonResponse($platformConfiguration->normalizeForExternalApi());
    }
}
