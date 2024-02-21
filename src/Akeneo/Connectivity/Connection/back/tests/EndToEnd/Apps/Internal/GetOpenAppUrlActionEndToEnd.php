<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetOpenAppUrlActionEndToEnd extends WebTestCase
{
    private FakeWebMarketplaceApi $webMarketplaceApi;
    private Connection $connection;
    private ConnectedAppLoader $connectedAppLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        /** @var FilePersistedFeatureFlags $featureFlags */
        $featureFlags = $this->get('feature_flags');
        $featureFlags->enable('marketplace_activate');

        $this->webMarketplaceApi = $this->get(WebMarketplaceApi::class);
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->connection = $this->get('database_connection');

        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
        $this->loadAppsFixtures();
    }

    public function test_it_returns_an_url_to_open_app(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            'a_client_id',
            'magento',
        );

        $this->flagTheAppWithOutdatedScopes('a_client_id');

        self::assertTrue($this->connectedAppHasOutdatedScopes('a_client_id'), 'Connected app should be flagged with outdated scopes');

        $this->client->request(
            'GET',
            '/rest/apps/connected-apps/magento/open-app-url',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();

        $expectedContent = [
            'url' => 'http://app.example.com/activate?pim_url=http%3A%2F%2Flocalhost%3A8080'
        ];

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertEquals($expectedContent, \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR));
        self::assertFalse($this->connectedAppHasOutdatedScopes('a_client_id'), 'Connected app should not be flagged with outdated scopes');
    }

    private function loadAppsFixtures(): void
    {
        $apps = [
            [
                'id' => 'a_client_id',
                'name' => 'Akeneo Shopware 6 Connector by EIKONA Media',
                'logo' => 'https://marketplace.akeneo.com/sites/default/files/styles/extension_logo_large/public/extension-logos/akeneo-to-shopware6-eimed_0.jpg?itok=InguS-1N',
                'author' => 'EIKONA Media GmbH',
                'partner' => 'Akeneo Preferred Partner',
                'description' => 'With the new "Akeneo-Shopware-6-Connector" from EIKONA Media, you can smoothly export all your product data from Akeneo to Shopware. The connector uses the standard interfaces provided for data exchange. Benefit from up-to-date product data in all your e-commerce channels and be faster on the market.',
                'url' => 'https://marketplace.akeneo.com/extension/akeneo-shopware-6-connector-eikona-media',
                'categories' => [
                    'E-commerce',
                ],
                'certified' => false,
                'activate_url' => 'http://app.example.com/activate',
                'callback_url' => 'http://app.example.com/callback',
            ],
        ];

        $this->webMarketplaceApi->setApps($apps);
    }

    private function flagTheAppWithOutdatedScopes(string $connectedAppId): void
    {
        $this->connection->executeQuery(
            'UPDATE akeneo_connectivity_connected_app SET has_outdated_scopes = 1 WHERE id = :id',
            [
                'id' => $connectedAppId,
            ],
        );
    }

    private function connectedAppHasOutdatedScopes(string $connectedAppId): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT has_outdated_scopes FROM akeneo_connectivity_connected_app WHERE id = :id',
            [
                'id' => $connectedAppId,
            ],
        );
    }
}
