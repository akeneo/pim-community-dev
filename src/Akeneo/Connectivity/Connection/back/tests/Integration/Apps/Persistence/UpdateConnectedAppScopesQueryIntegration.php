<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\CreateConnectedAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\UpdateConnectedAppScopesQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectedAppScopesQueryIntegration extends TestCase
{
    private UpdateConnectedAppScopesQuery $query;
    private ConnectionLoader $connectionLoader;
    private UserGroupLoader $userGroupLoader;
    private CreateConnectedAppQuery $createConnectedAppQuery;
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(UpdateConnectedAppScopesQuery::class);
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->userGroupLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_group_loader');
        $this->createConnectedAppQuery = $this->get(CreateConnectedAppQuery::class);
        $this->connection = $this->get('database_connection');
    }

    public function test_it_updates_connected_app_scopes(): void
    {
        $connection = $this->connectionLoader->createConnection('someConnectionCode', 'My Connector', FlowType::DATA_DESTINATION, false);
        $this->userGroupLoader->create(['name' => 'app_7891011ghijkl']);
        $connectedApp = new ConnectedApp(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'my app',
            ['scope 1', 'scope 2'],
            'someConnectionCode',
            'http://www.example.com/path/to/logo',
            'author',
            'app_7891011ghijkl',
            $connection->username(),
            ['category A'],
            true,
            null
        );
        $this->createConnectedAppQuery->execute($connectedApp);

        $this->query->execute(['scope 3', 'scope 1'], $connectedApp->getId());

        $row = $this->fetchApp('2677e764-f852-4956-bf9b-1a1ec1b0d145');

        Assert::assertSame([
            'id' => '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'name' => 'my app',
            'logo' => 'http://www.example.com/path/to/logo',
            'author' => 'author',
            'partner' => null,
            'categories' => '["category A"]',
            'certified' => '1',
            'connection_code' => 'someConnectionCode',
            'scopes' => '["scope 3", "scope 1"]',
            'user_group_name' => 'app_7891011ghijkl',
            'has_outdated_scopes' => '0'
        ], $row);
    }

    private function fetchApp(string $id): ?array
    {
        $query = <<<SQL
SELECT id, name, logo, author, partner, categories, certified, connection_code, scopes, user_group_name, has_outdated_scopes
FROM akeneo_connectivity_connected_app
WHERE id = :id
SQL;

        $row = $this->connection->fetchAssociative($query, [
            'id' => $id,
        ]);

        return $row ?: null;
    }
}
