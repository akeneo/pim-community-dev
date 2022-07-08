<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\SaveConnectedAppOutdatedScopesFlagQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveConnectedAppOutdatedScopesFlagQueryIntegration extends TestCase
{
    private SaveConnectedAppOutdatedScopesFlagQuery $query;
    private ConnectedAppLoader $connectedAppLoader;
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(SaveConnectedAppOutdatedScopesFlagQuery::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->connection = $this->get('database_connection');
    }

    public function test_it_updates_has_outdated_scopes_to_true(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'outdated_app_id',
            'outdated_app_code',
        );

        self::assertFalse($this->connectedAppHasOutdatedScopes('outdated_app_id'));

        $this->query->execute('outdated_app_id', true);

        self::assertTrue($this->connectedAppHasOutdatedScopes('outdated_app_id'));
    }

    public function test_it_updates_has_outdated_scopes_to_false(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'outdated_app_id',
            'outdated_app_code',
        );

        self::assertFalse($this->connectedAppHasOutdatedScopes('outdated_app_id'));

        $this->query->execute('outdated_app_id', false);

        self::assertFalse($this->connectedAppHasOutdatedScopes('outdated_app_id'));
    }

    private function connectedAppHasOutdatedScopes(string $connectedAppId): bool
    {
        $query = 'SELECT has_outdated_scopes FROM akeneo_connectivity_connected_app WHERE id = :id';

        return (bool) $this->connection->fetchOne($query, ['id' => $connectedAppId]);
    }
}
