<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllAppsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAllAppsQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApiInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllAppsQuery implements GetAllAppsQueryInterface
{
    private const MAX_REQUESTS = 10;

    public function __construct(
        private Connection $connection,
        private WebMarketplaceApiInterface $webMarketplaceApi,
        private int $pagination,
    ) {
    }

    public function execute(): GetAllAppsResult
    {
        $apps = [];
        $requests = 0;
        $offset = 0;

        $connectedAppsIds = $this->getAllConnectedAppsPublicIds();

        do {
            $result = $this->webMarketplaceApi->getApps($offset, $this->pagination);
            $requests++;
            $offset += $result['limit'];

            foreach ($result['items'] as $item) {
                $isConnected = \in_array($item['id'], $connectedAppsIds, true);
                $app = App::fromWebMarketplaceValues($item);
                $app = $app->withConnectedStatus($isConnected);
                $apps[] = $app;
            }
        } while (\count($result['items']) > 0 && \count($apps) < $result['total'] && $requests < self::MAX_REQUESTS);

        return GetAllAppsResult::create($result['total'], $apps);
    }

    /**
     * @return string[]
     */
    private function getAllConnectedAppsPublicIds(): array
    {
        $query = <<<SQL
SELECT pim_api_client.marketplace_public_app_id
FROM akeneo_connectivity_connected_app
JOIN akeneo_connectivity_connection ON akeneo_connectivity_connected_app.connection_code = akeneo_connectivity_connection.code
JOIN pim_api_client on akeneo_connectivity_connection.client_id = pim_api_client.id
SQL;

        return $this->connection->fetchFirstColumn($query);
    }
}
