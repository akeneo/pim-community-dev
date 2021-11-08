<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectedApp;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectedAppInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnection;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnectionInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Service\CreateUserInterface;
use Akeneo\Connectivity\Connection\Application\User\CreateUserGroupInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProviderInterface;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserRoleLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\DBAL\Connection;
use FOS\OAuthServerBundle\Storage\OAuthStorage;
use PHPUnit\Framework\Assert;
use Symfony\Component\Security\Core\User\UserInterface;

class DeleteAppHandlerIntegration extends TestCase
{
    private Connection $connection;
    private UserRoleLoader $userRoleLoader;
    private OAuthStorage $OAuthStorage;
    private ClientProviderInterface $clientProvider;
    private UserRepositoryInterface $userRepository;
    private DeleteAppHandler $deleteAppHandler;
    private CreateUserGroupInterface $createUserGroup;
    private CreateUserInterface $createUser;
    private CreateConnectionInterface $createConnection;
    private CreateConnectedAppInterface $createApp;
    private UnitOfWorkAndRepositoriesClearer $unitOfWorkAndRepositoriesClearer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->userRoleLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_role_loader');
        $this->OAuthStorage = $this->get('fos_oauth_server.storage');
        $this->clientProvider = $this->get('akeneo_connectivity.connection.service.apps.client_provider');
        $this->userRepository = $this->get('pim_user.repository.user');
        $this->deleteAppHandler = $this->get(DeleteAppHandler::class);
        $this->createUserGroup = $this->get('akeneo_connectivity.connection.service.user.create_user_group');
        $this->createUser = $this->get('akeneo_connectivity.connection.service.user.create_user');
        $this->createConnection = $this->get(CreateConnection::class);
        $this->createApp = $this->get(CreateConnectedApp::class);
        $this->unitOfWorkAndRepositoriesClearer = $this->get('pim_connector.doctrine.cache_clearer');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_to_delete_an_app(): void
    {
        $this->createConnectedApp('2677e764-f852-4956-bf9b-1a1ec1b0d145', 'magento');
        $this->createConnectedApp('0dfce574-2238-4b13-b8cc-8d257ce7645b', 'akeneo_print');

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
    }

    private function createConnectedApp(string $id, string $code): void
    {
        $marketplaceApp = App::fromWebMarketplaceValues([
            'id' => $id,
            'name' => $code,
            'logo' => 'http://example.com/logo.png',
            'author' => 'Akeneo',
            'url' => 'http://marketplace.akeneo.com/foo',
            'categories' => ['ecommerce'],
            'activate_url' => 'http://example.com/activate',
            'callback_url' => 'http://example.com/callback',
        ]);
        $client = $this->clientProvider->findOrCreateClient($marketplaceApp);
        $group = $this->createUserGroup->execute(sprintf('app_%s', $code));
        $role = $this->userRoleLoader->create([
            'role' => sprintf('ROLE_%s', strtoupper($code)),
            'label' => $code,
            'type' => 'app',
        ]);
        $user = $this->createUser->execute(
            $code,
            $marketplaceApp->getName(),
            ' ',
            [$group->getName()],
            [$role->getRole()]
        );
        $connection = $this->createConnection->execute(
            $code,
            $marketplaceApp->getName(),
            FlowType::OTHER,
            $client->getId(),
            $user->id(),
        );
        $this->createApp->execute(
            $marketplaceApp,
            ['read_products'],
            $connection->code(),
            $group->getName()
        );

        $user = $this->findConnectionUser($code);

        $this->OAuthStorage->createAuthCode($code, $client, $user, '', null);
        $this->OAuthStorage->createAccessToken($code, $client, $user, null);
        $this->OAuthStorage->createRefreshToken($code, $client, $user, null);

        $this->unitOfWorkAndRepositoriesClearer->clear();
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

    private function findConnectionUser(string $code): UserInterface
    {
        $query = <<<SQL
SELECT user_id
FROM akeneo_connectivity_connection
WHERE code = :code
SQL;

        $id = $this->connection->fetchOne($query, [
            'code' => $code,
        ]);

        return $this->userRepository->findOneById($id);
    }
}
