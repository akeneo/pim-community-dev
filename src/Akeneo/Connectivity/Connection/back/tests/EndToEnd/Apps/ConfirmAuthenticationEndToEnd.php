<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnection;
use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\DbalConnectedAppRepository;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Session\AppAuthorizationSession;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\User\CreateUserGroup;
use Akeneo\Connectivity\Connection\Infrastructure\User\Internal\CreateUser;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Test\Integration\Configuration;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfirmAuthenticationEndToEnd extends WebTestCase
{
    private FakeFeatureFlag $featureFlagMarketplaceActivate;
    private PropertyAccessor $propertyAccessor;
    private ClientManagerInterface $clientManager;
    private AppAuthorizationSession $appAuthorizationSession;
    private DbalConnectedAppRepository $repository;
    private CreateConnection $createConnection;
    private ClientProvider $clientProvider;
    private CreateUserGroup $createUserGroup;
    private CreateUser $createUser;
    private string $clientId = 'a_client_id';

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlagMarketplaceActivate = $this->get(
            'akeneo_connectivity.connection.marketplace_activate.feature'
        );
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->clientManager = $this->get('fos_oauth_server.client_manager.default');
        $this->appAuthorizationSession = $this->get(AppAuthorizationSession::class);
        $this->repository = $this->get(DbalConnectedAppRepository::class);
        $this->createConnection = $this->get(CreateConnection::class);
        $this->clientProvider = $this->get('akeneo_connectivity.connection.service.apps.client_provider');
        $this->createUserGroup = $this->get(CreateUserGroup::class);
        $this->createUser = $this->get('akeneo_connectivity.connection.service.user.create_user');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_redirect_url(): void
    {
        $authenticationScope = ScopeList::fromScopes([
            AuthenticationScope::SCOPE_OPENID,
            AuthenticationScope::SCOPE_PROFILE,
        ]);

        $this->featureFlagMarketplaceActivate->enable();

        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_open_apps');

        $this->createOAuth2Client(['marketplacePublicAppId' => $this->clientId]);

        $this->createConnectedApp($this->clientId);

        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => $this->clientId,
            'redirect_uri' => 'http://shopware.example.com/callback',
            'authorization_scope' => 'read_catalog_structure write_products',
            'authentication_scope' => $autenticationScope->toScopeString(),
            'state' => 'foo',
        ]);

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
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function test_it_throws_not_found_exception_with_feature_flag_disabled(): void
    {
        $this->featureFlagMarketplaceActivate->disable();

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

    public function test_it_redirects_if_not_xml_http_request(): void
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

        $this->createOAuth2Client(['marketplacePublicAppId' => $this->clientId]);

        $this->createConnectedApp($this->clientId);

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
        $autenticationScope = ScopeList::fromScopes([
            AuthenticationScope::SCOPE_OPENID,
            AuthenticationScope::SCOPE_PROFILE,
        ]);

        $this->featureFlagMarketplaceActivate->enable();

        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_open_apps');

        $this->createOAuth2Client(['marketplacePublicAppId' => $this->clientId]);

        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => $this->clientId,
            'redirect_uri' => 'http://shopware.example.com/callback',
            'authorization_scope' => 'read_catalog_structure write_products',
            'authentication_scope' => $autenticationScope->toScopeString(),
            'state' => 'foo',
        ]);

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
        $expected = [
            'errors' => [
                [
                    'message' => 'akeneo_connectivity.connection.connect.apps.constraint.client_id.must_be_valid',
                    'property_path' => 'clientId',
                ],
            ],
        ];

        $this->featureFlagMarketplaceActivate->enable();

        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_open_apps');

        $this->client->request(
            'POST',
            sprintf('/rest/apps/confirm-authentication/%s', 'a_random_client_id'),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals($expected, json_decode($response->getContent(), true));
    }

    private function createConnectedApp(string $appPublicId): void
    {
        $group = $this->createUserGroup->execute('userGroup_'.$appPublicId);

        $user = $this->createUser->execute(
            'username_'.$appPublicId,
            'firstname_'.$appPublicId,
            'lastname_'.$appPublicId,
            [$group->getName()]
        );

        $client = $this->clientProvider->findOrCreateClient(
            App::fromWebMarketplaceValues([
                'id' => $appPublicId,
                'name' => 'testName',
                'logo' => 'testLogo',
                'author' => 'testAuthor',
                'url' => 'testUrl',
                'categories' => [],
                'activate_url' => 'testUrl',
                'callback_url' => 'testUrl',
            ])
        );

        $this->createConnection->execute(
            'connectionCode_'.$appPublicId,
            'Connector_'.$appPublicId,
            FlowType::OTHER,
            $client->getId(),
            $user->id()
        );

        $this->repository->create(
            new ConnectedApp(
                $appPublicId,
                'App',
                [],
                'connectionCode_'.$appPublicId,
                'http://www.example.com/path/to/logo',
                'author',
                'userGroup_'.$appPublicId,
                [],
                false,
                'partner'
            )
        );
    }

    private function createOAuth2Client(array $data): ClientInterface
    {
        $client = $this->clientManager->createClient();
        foreach ($data as $key => $value) {
            $this->propertyAccessor->setValue($client, $key, $value);
        }
        $this->clientManager->updateClient($client);

        return $client;
    }
}
