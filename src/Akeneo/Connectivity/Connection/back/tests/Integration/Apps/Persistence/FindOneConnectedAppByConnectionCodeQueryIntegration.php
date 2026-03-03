<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindOneConnectedAppByConnectionCodeQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindOneConnectedAppByConnectionCodeQueryIntegration extends TestCase
{
    private FindOneConnectedAppByConnectionCodeQuery $query;
    private ConnectedAppLoader $connectedAppLoader;
    private DbalConnection $dbalConnection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(FindOneConnectedAppByConnectionCodeQuery::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->dbalConnection = $this->get('database_connection');
    }

    public function test_it_retrieves_an_app_by_connection_code(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'connected_app',
        );
        $expectedApp = [
            'id' => '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'name' => 'connected_app',
            'connection_code' => 'connected_app',
            'user_group_name' => 'app_connected_app',
            'connection_username' => $this->findConnectionUsername('connected_app'),
            'logo' => 'http://example.com/logo.png',
            'author' => 'Akeneo',
            'categories' => ['ecommerce'],
            'is_custom_app' => false,
            'scopes' => ['read_products'],
            'partner' => null,
            'certified' => false,
            'is_pending' => false,
            'has_outdated_scopes' => false,
        ];
        $connectedApp = $this->query->execute('connected_app');

        Assert::assertEquals($expectedApp, $connectedApp->normalize());
    }

    public function test_it_retrieves_a_custom_connected_app_by_connection_code(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'connected_custom_app',
            ['read_products'],
            true,
        );
        $expectedCustomApp = [
            'id' => '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'name' => 'connected_custom_app',
            'connection_code' => 'connected_custom_app',
            'author' => 'Akeneo',
            'logo' => null,
            'user_group_name' => 'app_connected_custom_app',
            'connection_username' => $this->findConnectionUsername('connected_custom_app'),
            'categories' => [],
            'partner' => null,
            'certified' => false,
            'is_custom_app' => true,
            'is_pending' => false,
            'scopes' => ['read_products'],
            'has_outdated_scopes' => false,
        ];
        $customApp = $this->query->execute('connected_custom_app');

        Assert::assertEquals($expectedCustomApp, $customApp->normalize());
    }

    public function test_it_retrieves_a_pending_app_by_connection_code(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'cc345scc-f852-4956-bf9b-1a1ec1b0d145',
            'pending_app',
            ['read_products'],
            false,
            true,
        );
        $expectedPendingApp = [
            'id' => 'cc345scc-f852-4956-bf9b-1a1ec1b0d145',
            'name' => 'pending_app',
            'scopes' => ['read_products'],
            'connection_code' => 'pending_app',
            'logo' => 'http://example.com/logo.png',
            'author' => 'Akeneo',
            'user_group_name' => 'app_pending_app',
            'connection_username' => $this->findConnectionUsername('pending_app'),
            'categories' => ['ecommerce'],
            'certified' => false,
            'partner' => null,
            'is_custom_app' => false,
            'is_pending' => true,
            'has_outdated_scopes' => false,
        ];
        $pendingApp = $this->query->execute('pending_app');

        Assert::assertEquals($expectedPendingApp, $pendingApp->normalize());
    }

    private function findConnectionUsername(string $code): string
    {
        $query = <<<SQL
SELECT oro_user.username
FROM akeneo_connectivity_connection
JOIN oro_user ON oro_user.id = akeneo_connectivity_connection.user_id
WHERE code = :code
SQL;

        return $this->dbalConnection->fetchOne($query, [
            'code' => $code,
        ]);
    }
}
