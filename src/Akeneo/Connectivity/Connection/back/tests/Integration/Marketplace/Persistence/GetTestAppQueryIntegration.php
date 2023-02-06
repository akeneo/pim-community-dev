<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Marketplace\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetCustomAppQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetTestAppQueryIntegration extends TestCase
{
    private Connection $connection;
    private ConnectedAppLoader $connectedAppLoader;
    private GetCustomAppQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->query = $this->get(GetCustomAppQuery::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_returns_a_test_app_with_an_user()
    {
        $this->createTestApp([
            'client_id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'client_secret' => 'foobar',
            'name' => 'My test app',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'user_id' => $this->findUserId('admin'),
        ]);

        $result = $this->query->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9');
        $this->assertEquals([
            'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'secret' => 'foobar',
            'name' => 'My test app',
            'author' => 'John Doe',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'connected' => false,
        ], $result);
    }

    public function test_it_returns_a_test_app_without_an_user()
    {
        $this->createTestApp([
            'client_id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'client_secret' => 'foobar',
            'name' => 'My test app',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'user_id' => null,
        ]);

        $result = $this->query->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9');
        $this->assertEquals([
            'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'secret' => 'foobar',
            'name' => 'My test app',
            'author' => null,
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'connected' => false,
        ], $result);
    }

    public function test_it_returns_a_test_app_which_is_connected()
    {
        $this->createTestApp([
            'client_id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'client_secret' => 'foobar',
            'name' => 'My test app',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'user_id' => null,
        ]);
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens('100eedac-ff5c-497b-899d-e2d64b6c59f9', 'foo');

        $result = $this->query->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9');
        $this->assertEquals([
            'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'secret' => 'foobar',
            'name' => 'My test app',
            'author' => null,
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'connected' => true,
        ], $result);
    }

    /**
     * @param array{
     *     client_id: string,
     *     client_secret: string,
     *     name: string,
     *     activate_url: string,
     *     callback_url: string,
     *     user_id: string|null,
     * } $data
     */
    private function createTestApp(array $data): void
    {
        $this->connection->insert('akeneo_connectivity_test_app', $data);
    }

    private function findUserId(?string $username): int
    {
        $query = <<<SQL
            SELECT id
            FROM oro_user
            WHERE username = :username
SQL;

        return (int) $this->connection->fetchOne($query, [
            'username' => $username,
        ]);
    }
}
