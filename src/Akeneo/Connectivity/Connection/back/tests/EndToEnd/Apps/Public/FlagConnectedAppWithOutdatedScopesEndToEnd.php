<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Public;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlagConnectedAppWithOutdatedScopesEndToEnd extends ApiTestCase
{
    private ConnectedAppLoader $connectedAppLoader;
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->connection = $this->get('database_connection');
    }

    public function test_it_flags_connected_app_scopes_as_outdated(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'outdated_app_id',
            'outdated_app_code',
        );

        $apiClient = $this->createAuthenticatedAppClient('outdated_app_code');
        $apiClient->request(
            'POST',
            '/connect/apps/v1/scopes/update?scopes=read_catalog_structure',
        );

        self::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());
        self::assertTrue($this->connectedAppHasOutdatedScopes('outdated_app_id'), 'Connected app scopes should be flagged as outdated');
    }

    private function createAuthenticatedAppClient(string $accessToken): KernelBrowser
    {
        static::ensureKernelShutdown();
        return static::createClient(['debug' => false], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$accessToken,
        ]);
    }

    private function connectedAppHasOutdatedScopes(string $connectedAppId): bool
    {
        $query = 'SELECT has_outdated_scopes FROM akeneo_connectivity_connected_app WHERE id = :id';

        return (bool) $this->connection->fetchOne($query, ['id' => $connectedAppId]);
    }
}
