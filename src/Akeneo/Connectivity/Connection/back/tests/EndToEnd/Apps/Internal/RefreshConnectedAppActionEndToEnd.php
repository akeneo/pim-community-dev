<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshConnectedAppActionEndToEnd extends WebTestCase
{
    private FakeFeatureFlag $featureFlagMarketplaceActivate;
    private ConnectedAppLoader $connectedAppLoader;
    private FakeWebMarketplaceApi $webMarketplaceApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlagMarketplaceActivate = $this->get(
            'akeneo_connectivity.connection.marketplace_activate.feature'
        );
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->webMarketplaceApi = $this->get(WebMarketplaceApi::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_refreshes_a_connected_app(): void
    {
        $this->featureFlagMarketplaceActivate->enable();
        $this->authenticateAsAdmin();

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'magento',
        );

        $this->webMarketplaceApi->setApps([
            [
                'id' => '2677e764-f852-4956-bf9b-1a1ec1b0d145',
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
                'activate_url' => 'http://shopware.example.com/activate',
                'callback_url' => 'http://shopware.example.com/callback',
            ],
        ]);

        $this->client->request(
            'POST',
            '/rest/apps/connected-apps/magento/refresh',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
