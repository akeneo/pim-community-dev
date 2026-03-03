<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindOneConnectedAppByUserIdentifierQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindOneConnectedAppByUserIdentifierQueryIntegration extends TestCase
{
    private FindOneConnectedAppByUserIdentifierQuery $query;
    private ConnectedAppLoader $connectedAppLoader;
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(FindOneConnectedAppByUserIdentifierQuery::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->connection = $this->get('database_connection');
    }

    public function test_it_returns_null(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'connected_app',
            ['read_products'],
            false,
        );

        $retrievedApp = $this->query->execute('unknown_user');

        Assert::assertNull($retrievedApp);
    }

    public function test_it_can_retrieve_an_app(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'connected_app',
            ['read_products'],
            false,
        );

        $userIdentifier = $this->getConnectionUserIdentifier('connected_app');

        $expectedApp = new ConnectedApp(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'connected_app',
            ['read_products'],
            'connected_app',
            'http://example.com/logo.png',
            'Akeneo',
            'app_connected_app',
            $userIdentifier,
            ['ecommerce'],
        );

        $retrievedApp = $this->query->execute($userIdentifier);

        Assert::assertEquals($expectedApp, $retrievedApp);
    }

    public function test_it_can_retrieve_a_connected_app_by_id_related_to_a_custom_app(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'connected_app',
            ['read_products'],
            true,
        );

        $userIdentifier = $this->getConnectionUserIdentifier('connected_app');

        $expectedApp = new ConnectedApp(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'connected_app',
            ['read_products'],
            'connected_app',
            null,
            'Akeneo',
            'app_connected_app',
            $userIdentifier,
            [],
            false,
            null,
            true,
        );

        $retrievedApp = $this->query->execute($userIdentifier);

        Assert::assertEquals($expectedApp, $retrievedApp);
    }

    private function getConnectionUserIdentifier(string $connectionCode): string
    {
        $selectQuery = <<<SQL
        SELECT oro_user.username
        FROM akeneo_connectivity_connection connection
        JOIN oro_user ON oro_user.id = connection.user_id
        WHERE connection.code = :connection_code
        SQL;

        return $this->connection->executeQuery($selectQuery, ['connection_code' => $connectionCode])->fetchOne();
    }
}
