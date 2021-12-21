<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps;

use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysHandler;
use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Session\AppAuthorizationSession;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfirmAuthenticationEndToEnd extends WebTestCase
{
    private FakeWebMarketplaceApi $webMarketplaceApi;
    private FakeFeatureFlag $featureFlagMarketplaceActivate;
    private GenerateAsymmetricKeysHandler $generateAsymmetricKeysHandler;
    private AppAuthorizationSession $appAuthorizationSession;
    private ClientProvider $clientProvider;

    private string $clientId = '90741597-54c5-48a1-98da-a68e7ee0a715';

    protected function setUp(): void
    {
        parent::setUp();
        $this->generateAsymmetricKeysHandler = $this->get(GenerateAsymmetricKeysHandler::class);
        $this->webMarketplaceApi = $this->get('akeneo_connectivity.connection.marketplace.web_marketplace_api');
        $this->featureFlagMarketplaceActivate = $this->get(
            'akeneo_connectivity.connection.marketplace_activate.feature'
        );
        $this->clientProvider = $this->get('akeneo_connectivity.connection.service.apps.client_provider');
        $this->appAuthorizationSession = $this->get(AppAuthorizationSession::class);

        $this->loadAppsFixtures();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_throws_not_found_exception_with_feature_flag_disabled(): void
    {
        $this->featureFlagMarketplaceActivate->disable();
        $this->authenticateAsAdmin();

        $this->client->request(
            'POST',
            sprintf('/rest/apps/confirm-authentication/%s', $this->clientId),
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_it_throws_access_denied_exception_with_missing_acl(): void
    {
        $this->featureFlagMarketplaceActivate->enable();
        $this->authenticateAsAdmin();
        $this->removeAclFromRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_open_apps');

        $this->client->request(
            'POST',
            sprintf('/rest/apps/confirm-authentication/%s', $this->clientId),
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function test_it_redirects_if_not_xmlhttp_request(): void
    {
        $this->featureFlagMarketplaceActivate->enable();
        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_open_apps');
        $this->client->followRedirects(false);
        $this->client->request(
            'POST',
            sprintf('/rest/apps/confirm-authentication/%s', $this->clientId),
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_FOUND, $response->getStatusCode());
    }

    public function test_it_throws_a_logic_exception_because_there_is_no_active_app_authorization_into_session(): void
    {
        $this->featureFlagMarketplaceActivate->enable();
        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_open_apps');

        $app = App::fromWebMarketplaceValues($this->webMarketplaceApi->getApp($this->clientId));
        $this->clientProvider->findOrCreateClient($app);

        $this->client->request(
            'POST',
            sprintf('/rest/apps/confirm-authentication/%s', $this->clientId),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function test_it_throws_a_logic_exception_because_there_is_no_connected_app(): void
    {
        $this->featureFlagMarketplaceActivate->enable();
        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_open_apps');

        $app = App::fromWebMarketplaceValues($this->webMarketplaceApi->getApp($this->clientId));
        $this->clientProvider->findOrCreateClient($app);

        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => $this->clientId,
            'redirect_uri' => 'http://shopware.example.com/callback',
            'authorization_scope' => 'read_catalog_structure write_products',
            'authentication_scope' => 'openid profile',
            'state' => 'foo',
        ]);

        $this->getConnectionLoader()->createConnection(
            'connectionCodeA',
            'Connector A',
            FlowType::DATA_DESTINATION,
            false
        );
        $this->getUserGroupLoader()->create(['name' => 'app_123456abcdef']);
        $this->getConnectedAppLoader()->createConnectedApp(
            $this->clientId,
            'App A',
            ['write_association_types'],
            'connectionCodeA',
            'http://www.example.com/path/to/logo/a',
            'author A',
            'app_123456abcdef',
            ['category A1', 'category A2'],
            false,
            'partner A'
        );

        $this->appAuthorizationSession->initialize($appAuthorization);

        $this->client->request(
            'POST',
            sprintf('/rest/apps/confirm-authentication/%s', $this->clientId),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function test_it_returns_an_error_because_app_validation_failed(): void
    {
        // TODO
    }

    public function test_it_returns_redirect_url(): void
    {
        // TODO
    }

    private function loadAppsFixtures(): void
    {
        $this->generateAsymmetricKeysHandler->handle(new GenerateAsymmetricKeysCommand());

        $apps = [
            [
                'id' => $this->clientId,
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
        ];

        $this->webMarketplaceApi->setApps($apps);
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
