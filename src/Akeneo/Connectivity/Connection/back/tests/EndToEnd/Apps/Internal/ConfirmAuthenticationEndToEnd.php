<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Apps\Internal;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnection;
use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\CreateConnectedAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Session\AppAuthorizationSession;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\User\CreateUser;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\User\CreateUserGroup;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfirmAuthenticationEndToEnd extends WebTestCase
{
    private FilePersistedFeatureFlags $featureFlags;
    private FakeWebMarketplaceApi $webMarketplaceApi;
    private PropertyAccessor $propertyAccessor;
    private ClientManagerInterface $clientManager;
    private AppAuthorizationSession $appAuthorizationSession;
    private CreateConnectedAppQuery $createConnectedAppQuery;
    private CreateConnection $createConnection;
    private ClientProvider $clientProvider;
    private CreateUserGroup $createUserGroup;
    private CreateUser $createUser;
    private string $clientId = 'a_client_id';

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlags = $this->get('feature_flags');
        $this->webMarketplaceApi = $this->get(WebMarketplaceApi::class);
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->clientManager = $this->get('fos_oauth_server.client_manager.default');
        $this->appAuthorizationSession = $this->get(AppAuthorizationSession::class);
        $this->createConnectedAppQuery = $this->get(CreateConnectedAppQuery::class);
        $this->createConnection = $this->get(CreateConnection::class);
        $this->clientProvider = $this->get(ClientProvider::class);
        $this->createUserGroup = $this->get(CreateUserGroup::class);
        $this->createUser = $this->get(CreateUser::class);

        $this->loadAppsFixtures();
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

        $this->featureFlags->enable('marketplace_activate');

        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_open_apps');

        $this->createOAuth2Client(['marketplacePublicAppId' => $this->clientId]);

        $this->createConnectedApp($this->clientId);

        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => $this->clientId,
            'redirect_uri' => 'http://shopware.example.com/callback',
            'authorization_scope' => 'read_catalog_structure write_products',
            'authentication_scope' => $authenticationScope->toScopeString(),
            'state' => 'foo',
        ]);

        $this->appAuthorizationSession->initialize($appAuthorization);

        $this->client->request(
            'POST',
            \sprintf('/rest/apps/confirm-authentication/%s', $this->clientId),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    private function createConnectedApp(string $appPublicId): void
    {
        $group = $this->createUserGroup->execute('userGroup_'.$appPublicId);

        $userId = $this->createUser->execute(
            'username_'.$appPublicId,
            'firstname_'.$appPublicId,
            [$group->getName()],
            ['ROLE_USER'],
            $appPublicId,
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
            $userId,
        );

        $this->createConnectedAppQuery->execute(
            new ConnectedApp(
                $appPublicId,
                'App',
                [],
                'connectionCode_'.$appPublicId,
                'http://www.example.com/path/to/logo',
                'author',
                'userGroup_'.$appPublicId,
                'username_'.$appPublicId,
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
                'activate_url' => 'http://shopware.example.com/activate',
                'callback_url' => 'http://shopware.example.com/callback',
            ],
        ];

        $this->webMarketplaceApi->setApps($apps);
    }
}
