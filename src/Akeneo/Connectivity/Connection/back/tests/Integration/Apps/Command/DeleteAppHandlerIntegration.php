<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class DeleteAppHandlerIntegration extends TestCase
{
    private Connection $connection;
    private ConnectedAppLoader $connectedAppLoader;
    private DeleteAppHandler $deleteAppHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->deleteAppHandler = $this->get(DeleteAppHandler::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_deletes_an_app(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'magento'
        );
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'akeneo_print'
        );

        Assert::assertSame([
            'akeneo_print',
            'magento',
        ], $this->findNameOfConnectedApps());
        Assert::assertSame([
            'akeneo_print',
            'magento',
        ], $this->findNameOfConnections());
        Assert::assertSame([
            'akeneo_print',
            'magento',
        ], $this->findNameOfUsers());
        /**
         * @todo https://akeneo.atlassian.net/browse/CXP-751 filter groups of type "app"
         */
        Assert::assertSame([
            'All',
            'app_akeneo_print',
            'app_magento',
            'IT support',
            'Manager',
            'Redactor',
        ], $this->findNameOfUserGroups());
        Assert::assertSame([
            'ROLE_AKENEO_PRINT',
            'ROLE_MAGENTO',
        ], $this->findNameOfUserRoles());
        Assert::assertSame([
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
        ], $this->findIdOfOAuthClients());
        Assert::assertSame([
            'akeneo_print',
            'magento',
        ], $this->findNameOfOAuthGrandCode());
        Assert::assertSame([
            'akeneo_print',
            'magento',
        ], $this->findNameOfOAuthAccessToken());
        Assert::assertSame([
            'akeneo_print',
            'magento',
        ], $this->findNameOfOAuthRefreshToken());
        Assert::assertSame([], $this->findRevokedAccessTokens());

        $this->deleteAppHandler->handle(new DeleteAppCommand('2677e764-f852-4956-bf9b-1a1ec1b0d145'));

        Assert::assertSame([
            'akeneo_print',
        ], $this->findNameOfConnectedApps(), 'Connected app was not deleted');
        Assert::assertSame([
            'akeneo_print',
        ], $this->findNameOfConnectedApps(), 'Connection was not deleted');
        Assert::assertSame([
            'akeneo_print',
        ], $this->findNameOfUsers(), 'User was not deleted');
        Assert::assertSame([
            'All',
            'app_akeneo_print',
            'IT support',
            'Manager',
            'Redactor',
        ], $this->findNameOfUserGroups(), 'User group was not deleted');
        Assert::assertSame([
            'ROLE_AKENEO_PRINT',
        ], $this->findNameOfUserRoles(), 'User role was not deleted');
        Assert::assertSame([
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
        ], $this->findIdOfOAuthClients(), 'OAuth client was not deleted');
        Assert::assertSame([
            'akeneo_print',
        ], $this->findNameOfOAuthGrandCode());
        Assert::assertSame([
            'akeneo_print',
        ], $this->findNameOfOAuthAccessToken());
        Assert::assertSame([
            'akeneo_print',
        ], $this->findNameOfOAuthRefreshToken());
        Assert::assertSame([
            'magento',
        ], $this->findRevokedAccessTokens());
    }

    private function findNameOfConnectedApps(): array
    {
        $query = <<<SQL
SELECT name
FROM akeneo_connectivity_connected_app
ORDER BY name
SQL;

        return $this->connection->fetchFirstColumn($query);
    }

    private function findNameOfConnections(): array
    {
        $query = <<<SQL
SELECT label
FROM akeneo_connectivity_connection
ORDER BY label
SQL;

        return $this->connection->fetchFirstColumn($query);
    }

    private function findNameOfUsers(): array
    {
        $query = <<<SQL
SELECT first_name
FROM oro_user
WHERE user_type = 'api'
ORDER BY first_name
SQL;

        return $this->connection->fetchFirstColumn($query);
    }

    private function findNameOfUserGroups(): array
    {
        $query = <<<SQL
SELECT name
FROM oro_access_group
ORDER BY name
SQL;

        return $this->connection->fetchFirstColumn($query);
    }

    private function findNameOfUserRoles(): array
    {
        $query = <<<SQL
SELECT role
FROM oro_access_role
WHERE type = 'app'
ORDER BY role
SQL;

        return $this->connection->fetchFirstColumn($query);
    }

    private function findIdOfOAuthClients(): array
    {
        $query = <<<SQL
SELECT marketplace_public_app_id
FROM pim_api_client
WHERE marketplace_public_app_id IS NOT NULL
ORDER BY marketplace_public_app_id
SQL;

        return $this->connection->fetchFirstColumn($query);
    }

    private function findNameOfOAuthGrandCode(): array
    {
        $query = <<<SQL
SELECT token
FROM pim_api_auth_code
ORDER BY token
SQL;

        return $this->connection->fetchFirstColumn($query);
    }

    private function findNameOfOAuthAccessToken(): array
    {
        $query = <<<SQL
SELECT token
FROM pim_api_access_token
ORDER BY token
SQL;

        return $this->connection->fetchFirstColumn($query);
    }

    private function findNameOfOAuthRefreshToken(): array
    {
        $query = <<<SQL
SELECT token
FROM pim_api_refresh_token
ORDER BY token
SQL;

        return $this->connection->fetchFirstColumn($query);
    }

    private function findRevokedAccessTokens(): array
    {
        $query = <<<SQL
SELECT token
FROM akeneo_connectivity_revoked_app_token
ORDER BY token
SQL;

        return $this->connection->fetchFirstColumn($query);
    }
}
