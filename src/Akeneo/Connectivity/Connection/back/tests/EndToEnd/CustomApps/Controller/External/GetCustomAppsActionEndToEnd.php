<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\CustomApps\Controller\External;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\CustomAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Security\AclLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External\GetCustomAppsAction
 */
class GetCustomAppsActionEndToEnd extends ApiTestCase
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

    public function test_it_lists_custom_apps(): void
    {
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
        $this->customAppLoader->create('3d5286d9-49b6-403f-aada-f891e18debc8', $userId, 'custom_app_1');
        $this->customAppLoader->create('897fa702-7321-4417-9ba5-ea908a4612bf', $userId, 'custom_app_2');
        $this->customAppLoader->create('d2173d05-7748-4fc6-8467-55d1eb84872b', $userId, 'custom_app_3');

        $client->request(
            'GET',
            '/api/rest/v1/test-apps',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $results = \json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        Assert::assertCount(3, $results);
        Assert::assertSame('3d5286d9-49b6-403f-aada-f891e18debc8', $results[0]['client_id']);
        Assert::assertSame('custom_app_1', $results[0]['name']);
        Assert::assertSame('897fa702-7321-4417-9ba5-ea908a4612bf', $results[1]['client_id']);
        Assert::assertSame('custom_app_2', $results[1]['name']);
        Assert::assertSame('d2173d05-7748-4fc6-8467-55d1eb84872b', $results[2]['client_id']);
        Assert::assertSame('custom_app_3', $results[2]['name']);
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
}
