<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAllConnectedAppsPublicIdsInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAllPendingAppsPublicIdsQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllAppsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllAppsQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllAppsQuery implements GetAllAppsQueryInterface
{
    private const MAX_REQUESTS = 10;

    public function __construct(
        private WebMarketplaceApiInterface $webMarketplaceApi,
        private GetAllConnectedAppsPublicIdsInterface $getAllConnectedAppsPublicIdsQuery,
        private GetAllPendingAppsPublicIdsQueryInterface $getAllPendingAppsPublicIdsQuery,
        private int $pagination
    ) {
    }

    public function execute(): GetAllAppsResult
    {
        $apps = [];
        $requests = 0;
        $offset = 0;

        $connectedAppsIds = $this->getAllConnectedAppsPublicIdsQuery->execute();
        $pendingAppsIds = $this->getAllPendingAppsPublicIdsQuery->execute();

        do {
            $result = $this->webMarketplaceApi->getApps($offset, $this->pagination);
            $requests++;
            $offset += $result['limit'];

            foreach ($result['items'] as $item) {
                $isConnected = \in_array($item['id'], $connectedAppsIds, true);
                $app = App::fromWebMarketplaceValues($item);
                $app = $app->withConnectedStatus($isConnected);
                if (\in_array($item['id'], $pendingAppsIds, true)) {
                    $app = $app->withIsPending();
                }
                $apps[] = $app;
            }
        } while (\count($result['items']) > 0 && \count($apps) < $result['total'] && $requests < self::MAX_REQUESTS);

        \usort($apps, fn ($a, $b): int => $a->isCertified() === $b->isCertified() ? 0 : ($a->isCertified() ? -1 : 1));

        return GetAllAppsResult::create($result['total'], $apps);
    }
}
