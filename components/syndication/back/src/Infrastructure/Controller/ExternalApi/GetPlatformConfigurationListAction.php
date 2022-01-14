<?php

namespace Akeneo\Platform\Syndication\Infrastructure\Controller\ExternalApi;

use Akeneo\Platform\Syndication\Domain\Model\PlatformConfiguration;
use Akeneo\Platform\Syndication\Domain\Query\PlatformConfiguration\FindPlatformConfigurationListQueryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetPlatformConfigurationListAction
{
    private FindPlatformConfigurationListQueryInterface $findPlatformConfigurationListQuery;

    public function __construct(
        FindPlatformConfigurationListQueryInterface $findPlatformConfigurationListQuery
    ) {
        $this->findPlatformConfigurationListQuery = $findPlatformConfigurationListQuery;
    }

    public function __invoke(): JsonResponse
    {
        $platformConfigurationList = $this->findPlatformConfigurationListQuery->execute();

        return new JsonResponse(array_map(function (PlatformConfiguration $platformConfiguration) {
            return $platformConfiguration->normalizeForExternalApi();
        }, $platformConfigurationList));
    }
}
