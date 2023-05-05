<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Validation;

use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AccessTokenRequest;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AccessTokenRequestValidationIntegration extends WebTestCase
{
    private ValidatorInterface $validator;
    private FakeWebMarketplaceApi $webMarketplaceApi;
    private FeatureFlags $featureFlags;
    private ClientProvider $clientProvider;
    private RequestAppAuthorizationHandler $appAuthorizationHandler;
    private string $clientId;

    public function test_it_validates_the_access_token_request(): void
    {
        $this->createApp();
        $authCode = $this->getAuthCode();

        $accessTokenRequest = new AccessTokenRequest($this->clientId, $authCode, 'authorization_code', '12345', '12345');
        $violations = $this->validator->validate($accessTokenRequest);

        Assert::assertCount(0, $violations);
    }

    public function test_it_invalidates_a_not_known_client_id(): void
    {
        $this->createApp();
        $authCode = $this->getAuthCode();

        $accessTokenRequest = new AccessTokenRequest('unknown_client_id', $authCode, 'authorization_code', '12345', '12345');
        $violations = $this->validator->validate($accessTokenRequest);

        $this->assertHasViolation($violations, 'clientId', 'invalid_client');
    }

    public function test_it_invalidates_a_blank_client_id(): void
    {
        $this->createApp();
        $authCode = $this->getAuthCode();

        $accessTokenRequest = new AccessTokenRequest('', $authCode, 'authorization_code', '12345', '12345');
        $violations = $this->validator->validate($accessTokenRequest);

        $this->assertHasViolation($violations, 'clientId', 'invalid_request');
    }

    public function test_it_invalidates_a_blank_code_identifier(): void
    {
        $this->createApp();
        $authCode = $this->getAuthCode();

        $accessTokenRequest = new AccessTokenRequest($this->clientId, $authCode, 'authorization_code', '', '12345');
        $violations = $this->validator->validate($accessTokenRequest);

        $this->assertHasViolation($violations, 'codeIdentifier', 'invalid_request');
    }

    public function test_it_invalidates_a_blank_code_challenge(): void
    {
        $this->createApp();
        $authCode = $this->getAuthCode();

        $accessTokenRequest = new AccessTokenRequest($this->clientId, $authCode, 'authorization_code', '12345', '');
        $violations = $this->validator->validate($accessTokenRequest);

        $this->assertHasViolation($violations, 'codeChallenge', 'invalid_request');
    }

    public function test_it_invalidates_a_blank_authorization_code(): void
    {
        $this->createApp();
        $authCode = $this->getAuthCode();

        $accessTokenRequest = new AccessTokenRequest($this->clientId, '', 'authorization_code', '12345', '12345');
        $violations = $this->validator->validate($accessTokenRequest);

        $this->assertHasViolation($violations, 'authorizationCode', 'invalid_request');
    }

    public function test_it_invalidates_a_not_known_authorization_code(): void
    {
        $this->createApp();
        $authCode = $this->getAuthCode();

        $accessTokenRequest = new AccessTokenRequest($this->clientId, 'unknown_auth_code', 'authorization_code', '12345', '12345');
        $violations = $this->validator->validate($accessTokenRequest);

        $this->assertHasViolation($violations, 'authorizationCode', 'invalid_grant');
    }

    public function test_it_invalidates_an_expired_authorization_code(): void
    {
        $this->createApp();
        $authCode = $this->getAuthCode();

        $this->expireCode($authCode);

        $accessTokenRequest = new AccessTokenRequest($this->clientId, $authCode, 'authorization_code', '12345', '12345');
        $violations = $this->validator->validate($accessTokenRequest);

        $this->assertHasViolation($violations, 'authorizationCode', 'invalid_grant');
    }

    public function test_it_invalidates_the_grant_type(): void
    {
        $this->createApp();
        $authCode = $this->getAuthCode();

        $accessTokenRequest = new AccessTokenRequest($this->clientId, $authCode, 'wrong_grant_type', '12345', '12345');
        $violations = $this->validator->validate($accessTokenRequest);

        $this->assertHasViolation($violations, 'grantType', 'unsupported_grant_type');
    }

    public function test_it_invalidates_a_wrong_code_challenge(): void
    {
        $this->webMarketplaceApi->setCodeChallengeResult(false);
        $this->createApp();
        $authCode = $this->getAuthCode();

        $accessTokenRequest = new AccessTokenRequest($this->clientId, $authCode, 'authorization_code', '12345', '12345');
        $violations = $this->validator->validate($accessTokenRequest);

        $this->assertHasViolation($violations, 'codeChallenge', 'invalid_client');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->get('validator');

        $this->webMarketplaceApi = $this->get(WebMarketplaceApi::class);
        $this->featureFlags = $this->get('feature_flags');
        $this->clientProvider = $this->get(ClientProvider::class);
        $this->appAuthorizationHandler = $this->get(RequestAppAuthorizationHandler::class);
        $this->clientId = '90741597-54c5-48a1-98da-a68e7ee0a715';
        $this->get('akeneo_connectivity.connection.marketplace_fake_apps.feature')->disable();
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

    private function assertHasViolation(
        ConstraintViolationList $constraintViolationList,
        string $propertyPath,
        string $message
    ): void {
        $violationFound = false;
        foreach ($constraintViolationList as $violation) {
            if ($violation->getPropertyPath() === $propertyPath && $violation->getMessage() === $message) {
                $violationFound = true;
                break;
            }
        }

        Assert::assertTrue($violationFound, \sprintf('The violation at property path "%s" has not been found.', $propertyPath));
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

    private function expireCode(string $code): void
    {
        $expirationTimestamp = \time() - 1;

        $query = <<<SQL
        UPDATE pim_api_auth_code
        SET expires_at = :expiration_timestamp
        WHERE token = :auth_code
        SQL;

        $this->get('database_connection')->executeQuery(
            $query,
            [
                'expiration_timestamp' => $expirationTimestamp,
                'auth_code' => $code,
            ]
        );

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }
}
