<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Marketplace\TestApps;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteTestAppActionEndToEnd extends WebTestCase
{
    private Connection $connection;
    private ConnectedAppLoader $connectedAppLoader;
    private FakeFeatureFlag $developerModeFlag;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->developerModeFlag = $this->get('akeneo_connectivity.connection.app_developer_mode.feature');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    public function test_it_creates_test_app(): void
    {
        $this->developerModeFlag->enable();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_test_apps');
        $this->authenticateAsAdmin();

        $testAppId = 'testAppId';

        $this->addTestApp($testAppId);
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens($testAppId, 'testConnectionCode');

        Assert::assertTrue($this->doesTestAppExists($testAppId), 'Test app should exist');
        Assert::assertTrue($this->doesConnectedAppExists($testAppId), 'Connected app should exist');

        $this->client->request(
            'DELETE',
            'rest/marketplace/test-apps/testAppId',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json'
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        Assert::assertFalse($this->doesTestAppExists($testAppId), "Test app shouldn't exist");
        Assert::assertFalse($this->doesConnectedAppExists($testAppId), "Connected app shouldn't exist");
    }

    private function addTestApp(string $id): void
    {
        $this->connection->insert('akeneo_connectivity_test_app', [
            'client_id' => $id,
            'client_secret' => $id,
            'name' => $id,
            'activate_url' => $id,
            'callback_url' => $id,
            'user_id' => null,
        ]);
    }

    private function doesTestAppExists(string $id): bool
    {
        $query = <<<SQL
        SELECT client_id
        FROM akeneo_connectivity_test_app
        WHERE client_id = :client_id
        SQL;

        $result = $this->connection->executeQuery($query, [
            'client_id' => $id,
        ])->fetchOne();

        return false !== $result;
    }

    private function doesConnectedAppExists(string $id): bool
    {
        $query = <<<SQL
        SELECT id
        FROM akeneo_connectivity_connected_app
        WHERE id = :id
        SQL;

        $result = $this->connection->executeQuery($query, [
            'id' => $id,
        ])->fetchOne();

        return false !== $result;
    }
}
