<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
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
class GetConnectedAppMonitoringSettingsActionEndToEnd extends WebTestCase
{
    private FakeFeatureFlag $featureFlagMarketplaceActivate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlagMarketplaceActivate = $this->get('akeneo_connectivity.connection.marketplace_activate.feature');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_gets_connected_app_monitoring_settings(): void
    {
        $this->featureFlagMarketplaceActivate->enable();
        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');

        /** @var ConnectionLoader $connectionLoader */
        $connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');

        $connectionLoader->createConnection('connectionCodeA', 'Connector A', FlowType::DATA_SOURCE, true);
        $connectionLoader->createConnection('connectionCodeB', 'Connector B', FlowType::DATA_DESTINATION, false);
        $connectionLoader->createConnection('connectionCodeC', 'Connector C', FlowType::OTHER, true);

        $expectedResult = [
            'flowType' => FlowType::DATA_SOURCE,
            'auditable' => true,
        ];

        $this->client->request(
            'GET',
            '/rest/apps/connected-apps/connectionCodeA/monitoring-settings',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();
        $result = \json_decode($response->getContent(), true);

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }
}
