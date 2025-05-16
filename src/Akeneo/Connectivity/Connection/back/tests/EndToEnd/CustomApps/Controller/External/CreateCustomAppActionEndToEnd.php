<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\CustomApps\Controller\External;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Security\AclLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\External\CreateCustomAppAction
 */
class CreateCustomAppActionEndToEnd extends ApiTestCase
{
    private Connection $connection;
    private AclLoader $aclLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->aclLoader = $this->get(AclLoader::class);
    }

    public function test_it_creates_a_custom_app(): void
    {
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

        $client->request('POST', '/api/rest/v1/test-apps', [], [], [], \json_encode([
            'name' => 'Custom App',
            'activate_url' => 'http://activate.test',
            'callback_url' => 'http://callback.test',
        ], JSON_THROW_ON_ERROR));

        $response = $client->getResponse();
        $responseContent = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        self::assertArrayHasKey('client_id', $responseContent);
        self::assertArrayHasKey('client_secret', $responseContent);
        self::assertIsString($responseContent['client_id']);
        self::assertIsString($responseContent['client_secret']);
        $this->assertCustomAppExists(
            $responseContent['client_id'],
            'Custom App',
            'http://activate.test',
            'http://callback.test'
        );
    }

    private function assertCustomAppExists(string $clientId, string $name, string $activateUrl, string $callbackUrl): void
    {
        $sql = <<<SQL
        SELECT 1
        FROM akeneo_connectivity_test_app
        WHERE client_id = :client_id 
          AND name = :name
          AND activate_url = :activate_url
          AND callback_url = :callback_url
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'client_id' => $clientId,
            'name' => $name,
            'activate_url' => $activateUrl,
            'callback_url' => $callbackUrl,
        ]);
        self::assertNotFalse($result, 'Test app should exist');
    }
}
