<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectedAppMonitoringSettingsActionEndToEnd extends WebTestCase
{
    private FilePersistedFeatureFlags $featureFlags;
    private ConnectionLoader $connectionLoader;
    private UserGroupLoader $userGroupLoader;
    private ConnectedAppLoader $connectedAppLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlags = $this->get('feature_flags');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->userGroupLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_group_loader');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_updates_connected_app_monitoring_settings(): void
    {
        $this->featureFlags->enable('marketplace_activate');
        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');

        $this->connectionLoader->createConnection(
            'connectionCodeA',
            'Connector A',
            FlowType::DATA_DESTINATION,
            false,
            ConnectionType::APP_TYPE
        );

        $this->userGroupLoader->create(['name' => 'app_7891011ghijkl']);

        $this->connectedAppLoader->createConnectedApp(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'App B',
            [],
            'connectionCodeA',
            'http://www.example.com/path/to/logo/b',
            'author B',
            'app_7891011ghijkl',
            [],
            true,
            null
        );

        $data = [
            'flowType' => FlowType::DATA_SOURCE,
            'auditable' => true,
        ];

        $this->client->request(
            'POST',
            '/rest/apps/connected-apps/connectionCodeA/monitoring-settings',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json'
            ],
            \json_encode($data)
        );
        $response = $this->client->getResponse();
        $result = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        Assert::assertNull($result);
        Assert::assertEquals($data, $this->getConnectionMonitoringSettings('connectionCodeA'));
    }

    private function getConnectionMonitoringSettings(string $connectionCode): array
    {
        $dbalConnection = $this->get('database_connection');
        $query = <<<SQL
SELECT flow_type, auditable
FROM akeneo_connectivity_connection
WHERE code = :code
SQL;

        $result = $dbalConnection->fetchAssociative($query, ['code' => $connectionCode]);

        return [
            'flowType' => $result['flow_type'],
            'auditable' => (bool) $result['auditable'],
        ];
    }
}
