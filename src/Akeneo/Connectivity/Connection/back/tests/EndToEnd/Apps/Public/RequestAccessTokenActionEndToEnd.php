<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Public;

use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\GenerateAsymmetricKeysHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class RequestAccessTokenActionEndToEnd extends WebTestCase
{
    private FakeWebMarketplaceApi $webMarketplaceApi;
    private FilePersistedFeatureFlags $featureFlags;
    private ClientProvider $clientProvider;
    private RequestAppAuthorizationHandler $appAuthorizationHandler;
    private string $clientId;
    private GenerateAsymmetricKeysHandler $generateAsymmetricKeysHandler;

    public function test_to_redeem_a_code_for_token(): void
    {
        $this->createApp();
        $authCode = $this->getAuthCode();

        $this->client->request(
            'POST',
            '/connect/apps/v1/oauth2/token',
            [
                'client_id' => $this->clientId,
                'code' => $authCode,
                'code_identifier' => 'any_code',
                'code_challenge' => 'code_challenge_hash',
                'grant_type' => 'authorization_code',
            ]
        );
        $response = $this->client->getResponse();
        $content = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertIsArray($content);
        Assert::assertArrayHasKey('access_token', $content);
        Assert::assertIsString($content['access_token']);
        Assert::assertArrayHasKey('token_type', $content);
        Assert::assertEquals('bearer', $content['token_type']);
        Assert::assertArrayHasKey('scope', $content);
        Assert::assertEquals('delete_products read_association_types write_catalog_structure', $content['scope']);
    }

    public function test_to_get_again_the_access_token(): void
    {
        $this->createApp();
        $authCode = $this->getAuthCode();

        $this->client->request(
            'POST',
            '/connect/apps/v1/oauth2/token',
            [
                'client_id' => $this->clientId,
                'code' => $authCode,
                'code_identifier' => 'any_code',
                'code_challenge' => 'code_challenge_hash',
                'grant_type' => 'authorization_code',
            ]
        );
        $creationResponse = $this->client->getResponse();
        Assert::assertEquals(Response::HTTP_OK, $creationResponse->getStatusCode());
        $creationContent = \json_decode($creationResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertArrayHasKey('access_token', $creationContent);
        Assert::assertIsString($creationContent['access_token']);
        $createdToken = $creationContent['access_token'];

        $secondResponse = $this->client->getResponse();
        $content = \json_decode($secondResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertIsArray($content);
        Assert::assertArrayHasKey('access_token', $content);
        Assert::assertSame($createdToken, $content['access_token']);
    }

    public function test_to_get_a_bad_request_if_the_request_is_wrong(): void
    {
        $this->createApp();
        $authCode = $this->getAuthCode();

        $this->client->request(
            'POST',
            '/connect/apps/v1/oauth2/token',
            [
                // No client_id
                'code' => $authCode,
                'code_identifier' => 'any_code',
                'code_challenge' => 'code_challenge_hash',
                'grant_type' => 'authorization_code',
            ]
        );
        $response = $this->client->getResponse();
        $content = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        Assert::assertSame('invalid_request', $content['error']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->webMarketplaceApi = $this->get(WebMarketplaceApi::class);
        $this->featureFlags = $this->get('feature_flags');
        $this->clientProvider = $this->get(ClientProvider::class);
        $this->generateAsymmetricKeysHandler = $this->get(GenerateAsymmetricKeysHandler::class);
        $this->appAuthorizationHandler = $this->get(RequestAppAuthorizationHandler::class);
        $this->clientId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $this->loadAppsFixtures();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createApp(): void
    {
        $appId = '90741597-54c5-48a1-98da-a68e7ee0a715';

        $this->featureFlags->enable('marketplace_activate');
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_apps');
        $this->authenticateAsAdmin();
        $app = App::fromWebMarketplaceValues($this->webMarketplaceApi->getApp($appId));
        $this->clientProvider->findOrCreateClient($app);
    }

    private function getAuthCode(): string
    {
        $appId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $this->appAuthorizationHandler->handle(new RequestAppAuthorizationCommand(
            $appId,
            'code',
            'write_catalog_structure delete_products read_association_types',
            'http://anyurl.test'
        ));

        $this->client->request(
            'POST',
            \sprintf('/rest/apps/confirm-authorization/%s', $appId),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();
        $responseContent = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertArrayHasKey('redirectUrl', $responseContent);

        $query = \parse_url($responseContent['redirectUrl'], PHP_URL_QUERY);
        \parse_str($query, $params);

        return $params['code'];
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
}
