<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\DeleteConnectedAppQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteConnectedAppQueryIntegration extends TestCase
{
    private Connection $connection;
    private ConnectedAppLoader $connectedAppLoader;
    private DeleteConnectedAppQuery $query;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->query = $this->get(DeleteConnectedAppQuery::class);
    }

    public function test_it_deletes_a_connected_app_from_the_database(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'magento'
        );

        $this->assertEquals(1, $this->countConnectedApps());

        $this->query->execute('2677e764-f852-4956-bf9b-1a1ec1b0d145');

        $this->assertEquals(0, $this->countConnectedApps());
    }

    private function countConnectedApps(): int
    {
        $query = <<<SQL
SELECT COUNT(*)
FROM akeneo_connectivity_connected_app
SQL;

        return (int) $this->connection->fetchOne($query);
    }
}
