<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\CustomApps\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\CustomAppLoader;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\Internal\RegenerateCustomAppSecretAction
 */
class RegenerateCustomAppSecretEndToEnd extends WebTestCase
{
    private ?FilePersistedFeatureFlags $featureFlags = null;
    private ?Connection $connection;
    private ?CustomAppLoader $customAppLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->customAppLoader = $this->get(CustomAppLoader::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_successfully_update_the_custom_app_secret(): void
    {
        $user = $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_test_apps');

        $this->customAppLoader->create('100eedac-ff5c-497b-899d-e2d64b6c59f9', $user->getId());

        $secretBefore = $this->getCustomAppSecret('100eedac-ff5c-497b-899d-e2d64b6c59f9');

        $this->client->request(
            'POST',
            '/rest/custom-apps/100eedac-ff5c-497b-899d-e2d64b6c59f9/secret',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        Assert::assertNotSame(
            $secretBefore,
            $this->getCustomAppSecret('100eedac-ff5c-497b-899d-e2d64b6c59f9')
        );
    }

    public function test_it_gets_acl_error(): void
    {
        $this->client->request(
            'POST',
            '/rest/custom-apps/0dfce574-2238-4b13-b8cc-8d257ce7645b/secret',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function test_it_gets_not_found_exception(): void
    {
        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_test_apps');

        $this->client->request(
            'POST',
            '/rest/custom-apps/0dfce574-2238-4b13-b8cc-000000000/secret',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    private function getCustomAppSecret(string $clientId): string
    {
        $sql = <<<SQL
        SELECT client_secret
        FROM akeneo_connectivity_test_app
        WHERE client_id = :clientId
        SQL;

        return $this->connection->fetchOne($sql, ['clientId' => $clientId]);
    }
}
