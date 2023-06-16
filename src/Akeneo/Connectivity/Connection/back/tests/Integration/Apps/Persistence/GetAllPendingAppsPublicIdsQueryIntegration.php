<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetAllPendingAppsPublicIdsQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class GetAllPendingAppsPublicIdsQueryIntegration extends TestCase
{
    private ConnectedAppLoader $connectedAppLoader;
    private GetAllPendingAppsPublicIdsQuery $query;
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->query = $this->get(GetAllPendingAppsPublicIdsQuery::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    public function test_it_returns_empty_array_when_no_pending_app_exists(): void
    {
        $result = $this->query->execute();

        $this->assertEmpty($result);
    }

    public function test_it_returns_pending_app_codes(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'foo'
        );

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2777e764-f852-4956-bf9b-1a1ec1b0d146',
            'bar'
        );

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2877e764-f852-4956-bf9b-1a1ec1b0d147',
            'baz'
        );

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2468e764-f852-4956-bf9b-1a1ec1b0d147',
            'buz'
        );

        $this->makeAppsPending(['2777e764-f852-4956-bf9b-1a1ec1b0d146', '2877e764-f852-4956-bf9b-1a1ec1b0d147']);

        $result = $this->query->execute();

        $this->assertEqualsCanonicalizing([
            '2777e764-f852-4956-bf9b-1a1ec1b0d146',
            '2877e764-f852-4956-bf9b-1a1ec1b0d147',
        ], $result);
    }

    private function makeAppsPending(array $ids): void
    {
        $sql = <<<SQL
DELETE FROM pim_api_access_token WHERE client IN (
    SELECT id
    FROM pim_api_client
    WHERE marketplace_public_app_id IN (:ids)
)
SQL;

        $this->connection->executeQuery($sql, ['ids' => $ids], ['ids' => Connection::PARAM_STR_ARRAY]);
    }
}
