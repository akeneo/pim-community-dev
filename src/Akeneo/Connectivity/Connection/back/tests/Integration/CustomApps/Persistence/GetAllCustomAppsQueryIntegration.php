<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\CustomApps\DTO\GetAllCustomAppsResult;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetAllCustomAppsQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\GetAllCustomAppsQuery
 */
class GetAllCustomAppsQueryIntegration extends TestCase
{
    private ?Connection $connection;
    private ?ConnectedAppLoader $connectedAppLoader;
    private ?GetAllCustomAppsQuery $getAllCustomAppsQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->getAllCustomAppsQuery = $this->get(GetAllCustomAppsQuery::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_it_returns_custom_apps(): void
    {
        $this->createCustomApp([
            'client_id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
            'client_secret' => 'foobar',
            'name' => 'My test app',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'user_id' => $this->findUserId('admin'),
        ]);
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens('100eedac-ff5c-497b-899d-e2d64b6c59f9', 'foo');
        $this->createCustomApp([
            'client_id' => '42b9ecb1-ddd7-4874-9ad6-21a02d08ed50',
            'client_secret' => 'foobar',
            'name' => 'My test app 2',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'user_id' => null,
        ]);

        $result = $this->getAllCustomAppsQuery->execute();

        $this->assertEquals(
            GetAllCustomAppsResult::create(2, [
                App::fromCustomAppValues([
                    'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                    'name' => 'My test app',
                    'author' => 'John Doe',
                    'activate_url' => 'http://shopware.example.com/activate',
                    'callback_url' => 'http://shopware.example.com/callback',
                    'connected' => true,
                ]),
                App::fromCustomAppValues([
                    'id' => '42b9ecb1-ddd7-4874-9ad6-21a02d08ed50',
                    'name' => 'My test app 2',
                    'author' => null,
                    'activate_url' => 'http://shopware.example.com/activate',
                    'callback_url' => 'http://shopware.example.com/callback',
                    'connected' => false,
                ]),
            ]),
            $result
        );
    }

    /**
     * @param array{
     *     client_id: string,
     *     client_secret: string,
     *     name: string,
     *     activate_url: string,
     *     callback_url: string,
     *     user_id: int|null,
     * } $data
     */
    private function createCustomApp(array $data): void
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
