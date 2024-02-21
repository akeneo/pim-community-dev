<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetConnectedAppActionEndToEnd extends WebTestCase
{
    private FilePersistedFeatureFlags $featureFlags;

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlags = $this->get('feature_flags');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_gets_connected_app(): void
    {
        $this->featureFlags->enable('marketplace_activate');
        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');

        $this->getConnectionLoader()->createConnection('connectionCodeB', 'Connector B', FlowType::DATA_DESTINATION, false);
        $this->getUserGroupLoader()->create(['name' => 'app_7891011ghijkl']);
        $this->getConnectedAppLoader()->createConnectedApp(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'App B',
            ['scope B1', 'scope B2'],
            'connectionCodeB',
            'http://www.example.com/path/to/logo/b',
            'author B',
            'app_7891011ghijkl',
            ['category B1'],
            true,
            null
        );

        $connectionA = $this->getConnectionLoader()->createConnection('connectionCodeA', 'Connector A', FlowType::DATA_DESTINATION, false);
        $this->getUserGroupLoader()->create(['name' => 'app_123456abcdef']);
        $this->getConnectedAppLoader()->createConnectedApp(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'App A',
            ['scope A1'],
            'connectionCodeA',
            'http://www.example.com/path/to/logo/a',
            'author A',
            'app_123456abcdef',
            ['category A1', 'category A2'],
            false,
            'partner A'
        );

        $expectedResult = [
            'id' => '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'name' => 'App A',
            'scopes' => ['scope A1'],
            'connection_code' => 'connectionCodeA',
            'logo' => 'http://www.example.com/path/to/logo/a',
            'author' => 'author A',
            'user_group_name' => 'app_123456abcdef',
            'connection_username' => $connectionA->username(),
            'categories' => ['category A1', 'category A2'],
            'certified' => false,
            'partner' => 'partner A',
            'is_custom_app' => false,
            'is_pending' => false,
            'has_outdated_scopes' => false,
        ];

        $this->client->request(
            'GET',
            '/rest/apps/connected-apps/connectionCodeA',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();
        $result = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    private function getConnectionLoader(): ConnectionLoader
    {
        return $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
    }

    private function getConnectedAppLoader(): ConnectedAppLoader
    {
        return $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
    }

    private function getUserGroupLoader(): UserGroupLoader
    {
        return $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_group_loader');
    }
}
