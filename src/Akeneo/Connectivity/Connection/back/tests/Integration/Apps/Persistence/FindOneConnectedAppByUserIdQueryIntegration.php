<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindOneConnectedAppByUserIdQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindOneConnectedAppByUserIdQueryIntegration extends TestCase
{
    private FindOneConnectedAppByUserIdQuery $query;
    private ConnectedAppLoader $connectedAppLoader;
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(FindOneConnectedAppByUserIdQuery::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->connection = $this->get('database_connection');
    }

    public function test_it_returns_connected_app_associated_to_the_user(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'outdated_app_id',
            'outdated_app_code',
        );
        $userId = $this->getUserId('outdated_app_code');

        $connectedApp = $this->query->execute($userId);

        self::assertNotNull($connectedApp, 'Should return connected app associated to the user');
        self::assertEquals('outdated_app_id', $connectedApp->getId());
    }

    public function test_it_returns_null_on_non_app_associated_user(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'outdated_app_id',
            'outdated_app_code',
        );

        $adminUser = $this->createAdminUser();

        $connectedApp = $this->query->execute($adminUser->getId());

        self::assertNull($connectedApp, 'Should return null for non associated users');
    }

    private function getUserId(string $firstName): int
    {
        $query = 'SELECT id FROM oro_user WHERE first_name = :firstname';

        return (int) $this->connection->fetchOne($query, ['firstname' => $firstName]);
    }
}
