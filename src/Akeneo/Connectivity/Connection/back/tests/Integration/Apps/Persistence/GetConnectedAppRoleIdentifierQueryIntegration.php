<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetConnectedAppRoleIdentifierQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetConnectedAppRoleIdentifierQueryIntegration extends TestCase
{
    private GetConnectedAppRoleIdentifierQuery $query;
    private ConnectedAppLoader $connectedAppLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetConnectedAppRoleIdentifierQuery::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    public function test_it_returns_null_on_unknown_app(): void
    {
        $roleIdentifier = $this->query->execute('some_app');

        self::assertNull($roleIdentifier, 'Should return null on unknown app');
    }

    public function test_it_returns_role_identifier_for_a_connected_app(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens('connected_app_id', 'connection_code');

        $roleIdentifier = $this->query->execute('connected_app_id');

        self::assertEquals('ROLE_CONNECTION_CODE', $roleIdentifier, 'Should return connected app role');
    }
}
