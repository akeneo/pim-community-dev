<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\UserConsentRequiredException;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\SystemClock;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\UserConsentLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestAppAuthenticationHandlerIntegration extends TestCase
{
    private RequestAppAuthenticationHandler $requestAppAuthenticationHandler;
    private ClientManagerInterface $clientManager;
    private PropertyAccessor $propertyAccessor;
    private Connection $connection;
    private UserConsentLoader $userConsentLoader;
    private FakeClock $clock;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestAppAuthenticationHandler = $this->get(RequestAppAuthenticationHandler::class);
        $this->clientManager = $this->get('fos_oauth_server.client_manager.default');
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->connection = $this->get('database_connection');
        $this->userConsentLoader = $this->get(UserConsentLoader::class);
        $this->clock = $this->get(SystemClock::class);
    }

    public function test_it_consents_automatically_when_openid_is_the_only_scope_requested(): void
    {
        $this->createOAuth2Client([
            'marketplacePublicAppId' => 'e4d35502-08c9-40b4-a378-05d4cb255862',
        ]);
        $user = $this->createAdminUser();

        $command = new RequestAppAuthenticationCommand(
            'e4d35502-08c9-40b4-a378-05d4cb255862',
            $user->getId(),
            ScopeList::fromScopeString('openid')
        );
        $this->requestAppAuthenticationHandler->handle($command);

        $result = $this->connection->fetchAssociative(
            'SELECT * FROM akeneo_connectivity_user_consent WHERE app_id = :appId AND user_id = :userId',
            [
                'appId' => 'e4d35502-08c9-40b4-a378-05d4cb255862',
                'userId' => $user->getId()
            ]
        );

        $this->assertEquals(['openid'], \json_decode($result['scopes'], true, 512, JSON_THROW_ON_ERROR));
    }

    public function test_it_consents_automatically_when_less_scopes_are_requested(): void
    {
        $this->createOAuth2Client([
            'marketplacePublicAppId' => 'e4d35502-08c9-40b4-a378-05d4cb255862',
        ]);
        $user = $this->createAdminUser();
        $this->userConsentLoader->addUserConsent(
            $user->getId(),
            'e4d35502-08c9-40b4-a378-05d4cb255862',
            ['openid', 'email', 'profile'],
            Uuid::uuid4(),
            $this->clock->now()
        );

        $command = new RequestAppAuthenticationCommand(
            'e4d35502-08c9-40b4-a378-05d4cb255862',
            $user->getId(),
            ScopeList::fromScopeString('openid email')
        );
        $this->requestAppAuthenticationHandler->handle($command);

        $result = $this->connection->fetchAssociative(
            'SELECT * FROM akeneo_connectivity_user_consent WHERE app_id = :appId AND user_id = :userId',
            [
                'appId' => 'e4d35502-08c9-40b4-a378-05d4cb255862',
                'userId' => $user->getId()
            ]
        );

        $this->assertEquals(['openid', 'email'], \json_decode($result['scopes'], true, 512, JSON_THROW_ON_ERROR));
    }

    public function test_it_throws_when_new_scopes_are_requiring_consent(): void
    {
        $this->createOAuth2Client([
            'marketplacePublicAppId' => 'e4d35502-08c9-40b4-a378-05d4cb255862',
        ]);
        $user = $this->createAdminUser();

        $this->expectExceptionObject(
            new UserConsentRequiredException('e4d35502-08c9-40b4-a378-05d4cb255862', $user->getId())
        );

        $command = new RequestAppAuthenticationCommand(
            'e4d35502-08c9-40b4-a378-05d4cb255862',
            $user->getId(),
            ScopeList::fromScopeString('openid profile email'),
        );
        $this->requestAppAuthenticationHandler->handle($command);
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
