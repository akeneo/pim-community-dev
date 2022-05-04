<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class DeleteAppActionEndToEnd extends WebTestCase
{
    private ConnectedAppLoader $connectedAppLoader;
    private Connection $connection;

    public function test_it_successfully_deletes_the_app(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'magento',
        );

        Assert::assertEquals(1, $this->countConnectedApps());

        $this->client->request(
            'DELETE',
            '/rest/apps/connected-apps/magento',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        Assert::assertEquals(0, $this->countConnectedApps());
    }

    private function countConnectedApps(): int
    {
        $query = <<<SQL
SELECT COUNT(*)
FROM akeneo_connectivity_connected_app
SQL;

        return (int) $this->connection->fetchOne($query);
    }

    protected function setUp(): void
    {
        parent::setUp();

        /** @var FilePersistedFeatureFlags $featureFlags */
        $featureFlags = $this->get('feature_flags')->enable('marketplace_activate');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->connection = $this->get('database_connection');

        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
