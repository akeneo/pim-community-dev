<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Marketplace\TestApps\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class DeleteTestAppActionEndToEnd extends WebTestCase
{
    private FilePersistedFeatureFlags $featureFlags;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlags = $this->get('feature_flags');
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_successfully_deletes_the_test_app(): void
    {
        $appId = $this->createTestApp();

        Assert::assertEquals(1, $this->countTestApps());

        $this->client->request(
            'DELETE',
            \sprintf('/rest/marketplace/test-apps/%s', $appId),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        Assert::assertEquals(0, $this->countTestApps());
    }

    private function createTestApp(): string
    {
        $this->featureFlags->enable('app_developer_mode');
        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_test_apps');

        $this->client->request(
            'POST',
            'rest/marketplace/test-apps',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json'
            ],
            \json_encode([
                'name' => 'Test app bynder',
                'callbackUrl' => 'http://any_url.test',
                'activateUrl' => 'http://activate.test',
            ])
        );

        $response = $this->client->getResponse();
        $createdResult = \json_decode($response->getContent(), true);

        return $createdResult['clientId'];
    }

    private function countTestApps(): int
    {
        $query = <<<SQL
SELECT COUNT(*)
FROM akeneo_connectivity_test_app
SQL;

        return (int) $this->connection->fetchOne($query);
    }
}
