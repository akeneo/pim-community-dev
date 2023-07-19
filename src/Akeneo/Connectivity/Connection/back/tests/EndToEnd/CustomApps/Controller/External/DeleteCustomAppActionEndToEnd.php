<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\CustomApps\Controller\External;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\CustomAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Security\AclLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External\DeleteCustomAppAction
 */
class DeleteCustomAppActionEndToEnd extends ApiTestCase
{
    private ?Connection $connection;
    private ?AclLoader $aclLoader;
    private ?CustomAppLoader $customAppLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->aclLoader = $this->get(AclLoader::class);
        $this->customAppLoader = $this->get(CustomAppLoader::class);
    }

    public function test_it_deletes_a_custom_app(): void
    {
        $clientId = 'test_client_id';

        $this->aclLoader->addAclToRoles('akeneo_connectivity_connection_manage_test_apps', ['ROLE_ADMINISTRATOR']);

        $connection = $this->createConnection();
        $client = $this->createAuthenticatedClient(
            [],
            [],
            $connection->clientId(),
            $connection->secret(),
            $connection->username(),
            $connection->password()
        );

        $userId = $this->getUserIdByUsername($connection->username());
        $this->customAppLoader->create($clientId, $userId);

        self::assertTrue($this->customAppExists($clientId), 'Custom app should exist');

        $client->request('DELETE', \sprintf('/api/rest/v1/test-apps/%s', $clientId));

        self::assertEquals(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());

        self::assertFalse($this->customAppExists($clientId), 'Custom app should be removed');
    }

    private function getUserIdByUsername(string $username): int
    {
        $sql = <<<SQL
        SELECT id
        FROM oro_user
        WHERE username = :username
        SQL;

        $userId = $this->connection->fetchOne($sql, ['username' => $username]);

        if ($userId === false) {
            throw new \LogicException('User not found');
        }

        return (int) $userId;
    }

    private function customAppExists(string $clientId): bool
    {
        $sql = <<<SQL
        SELECT 1
        FROM akeneo_connectivity_test_app
        WHERE client_id = :client_id
        SQL;

        return (bool) $this->connection->fetchOne($sql, ['client_id' => $clientId]);
    }
}
