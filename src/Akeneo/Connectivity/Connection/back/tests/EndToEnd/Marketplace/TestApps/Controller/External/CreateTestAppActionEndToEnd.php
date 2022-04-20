<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Marketplace\TestApps\Controller\External;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Security\AclLoader;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateTestAppActionEndToEnd extends ApiTestCase
{
    private FakeFeatureFlag $developerModeFeatureFlag;
    private Connection $connection;
    private AclLoader $aclLoader;

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->developerModeFeatureFlag = $this->get('akeneo_connectivity.connection.app_developer_mode.feature');
        $this->connection = $this->get('database_connection');
        $this->aclLoader = $this->get(AclLoader::class);
    }

    public function test_it_creates_a_test_app(): void
    {
        $this->developerModeFeatureFlag->enable();
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
            'name' => 'Test App',
            'activateUrl' => 'http://activate.test',
            'callbackUrl' => 'http://callback.test',
        ]));

        $response = $client->getResponse();
        $responseContent = \json_decode($response->getContent(), true);

        Assert::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        self::assertArrayHasKey('client_id', $responseContent);
        self::assertArrayHasKey('client_secret', $responseContent);
        self::assertIsString($responseContent['client_id']);
        self::assertIsString($responseContent['client_secret']);
        $this->assertTestAppExists(
            $responseContent['client_id'],
            'Test App',
            'http://activate.test',
            'http://callback.test'
        );
    }

    private function assertTestAppExists(string $clientId, string $name, string $activateUrl, string $callbackUrl): void
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
        Assert::assertNotFalse($result, 'Test app should exist');
    }
}
