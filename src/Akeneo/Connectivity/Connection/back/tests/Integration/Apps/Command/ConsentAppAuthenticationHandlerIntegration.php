<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnection;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthenticationException;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProvider;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\CreateConnectedAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetUserConsentedAuthenticationScopesQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Session\AppAuthorizationSession;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\User\CreateUser;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\User\CreateUserGroup;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsentAppAuthenticationHandlerIntegration extends TestCase
{
    private ClientManagerInterface $clientManager;
    private PropertyAccessor $propertyAccessor;
    private ConsentAppAuthenticationHandler $handler;
    private AppAuthorizationSession $appAuthorizationSession;
    private CreateConnectedAppQuery $createConnectedAppQuery;
    private CreateConnection $createConnection;
    private ClientProvider $clientProvider;
    private CreateUserGroup $createUserGroup;
    private CreateUser $createUser;
    private GetUserConsentedAuthenticationScopesQuery $getUserConsentedAuthenticationScopesQuery;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientManager = $this->get('fos_oauth_server.client_manager.default');
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->handler = $this->get(ConsentAppAuthenticationHandler::class);
        $this->appAuthorizationSession = $this->get(AppAuthorizationSession::class);
        $this->createConnectedAppQuery = $this->get(CreateConnectedAppQuery::class);
        $this->createConnection = $this->get(CreateConnection::class);
        $this->clientProvider = $this->get(ClientProvider::class);
        $this->createUserGroup = $this->get(CreateUserGroup::class);
        $this->createUser = $this->get(CreateUser::class);
        $this->getUserConsentedAuthenticationScopesQuery = $this->get(GetUserConsentedAuthenticationScopesQuery::class);
    }

    public function test_it_creates_the_user_consent(): void
    {
        $appId = 'an_app_id';
        $autenticationScope = ScopeList::fromScopes([
            AuthenticationScope::SCOPE_OPENID,
            AuthenticationScope::SCOPE_PROFILE,
        ]);

        $this->createOAuth2Client(['marketplacePublicAppId' => $appId]);
        $user = $this->createAdminUser();
        $this->createConnectedApp($appId);

        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => $appId,
            'redirect_uri' => 'http://shopware.example.com/callback',
            'authorization_scope' => 'read_catalog_structure write_products',
            'authentication_scope' => $autenticationScope->toScopeString(),
            'state' => 'foo',
        ]);

        $this->appAuthorizationSession->initialize($appAuthorization);

        $this->handler->handle(new ConsentAppAuthenticationCommand($appId, $user->getId()));

        $result = $this->getUserConsentedAuthenticationScopesQuery->execute($user->getId(), $appId);

        Assert::assertIsArray($result);
        Assert::assertEquals($autenticationScope, ScopeList::fromScopes($result));
    }

    public function test_it_throws_invalid_argument_exception_because_command_validation_has_failed(): void
    {
        $anUnknowAppId = 'an_app_id';
        $anUnknownPimUserId = 15;

        $this->expectException(InvalidAppAuthenticationException::class);
        $this->expectExceptionMessage('akeneo_connectivity.connection.connect.apps.constraint.client_id.must_be_valid');

        $this->handler->handle(new ConsentAppAuthenticationCommand($anUnknowAppId, $anUnknownPimUserId));
    }

    public function test_it_throws_logic_exception_because_of_missing_app_authorization_in_session(): void
    {
        $appId = 'an_app_id';

        $this->createOAuth2Client(['marketplacePublicAppId' => $appId]);
        $user = $this->createAdminUser();
        $this->createConnectedApp($appId);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('There is no active app authorization in session');

        $this->handler->handle(new ConsentAppAuthenticationCommand($appId, $user->getId()));
    }

    public function test_it_throws_logic_exception_because_of_missing_connected_app_into_database(): void
    {
        $appId = 'an_app_id';
        $autenticationScope = ScopeList::fromScopes([
            AuthenticationScope::SCOPE_OPENID,
            AuthenticationScope::SCOPE_PROFILE,
        ]);

        $this->createOAuth2Client(['marketplacePublicAppId' => $appId]);
        $user = $this->createAdminUser();

        $appAuthorization = AppAuthorization::createFromNormalized([
            'client_id' => $appId,
            'redirect_uri' => 'http://shopware.example.com/callback',
            'authorization_scope' => 'read_catalog_structure write_products',
            'authentication_scope' => $autenticationScope->toScopeString(),
            'state' => 'foo',
        ]);

        $this->appAuthorizationSession->initialize($appAuthorization);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The connected app should have been created');

        $this->handler->handle(new ConsentAppAuthenticationCommand($appId, $user->getId()));
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
}
