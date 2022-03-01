<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectedAppMonitoringSettingsActionEndToEnd extends WebTestCase
{
    private FakeFeatureFlag $featureFlagMarketplaceActivate;
    private ConnectionLoader $connectionLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlagMarketplaceActivate = $this->get('akeneo_connectivity.connection.marketplace_activate.feature');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_updates_connected_app_monitoring_settings(): void
    {
        $this->featureFlagMarketplaceActivate->enable();
        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');

        $this->connectionLoader->createConnection(
            'connectionCodeA',
            'Connector A',
            FlowType::DATA_DESTINATION,
            false,
            ConnectionType::APP_TYPE
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
        $result = \json_decode($response->getContent(), true);

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
