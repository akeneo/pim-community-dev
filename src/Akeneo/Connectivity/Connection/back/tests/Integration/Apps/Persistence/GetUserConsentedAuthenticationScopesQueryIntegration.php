<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\GetUserConsentedAuthenticationScopesQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\SystemClock;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\UserConsentLoader;
use Akeneo\Test\Integration\Configuration;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUserConsentedAuthenticationScopesQueryIntegration extends WebTestCase
{
    private GetUserConsentedAuthenticationScopesQuery $getUserConsentedAuthenticationScopeQuery;
    private UserConsentLoader $userConsentLoader;
    private FakeClock $clock;
    private ConnectionLoader $connectionLoader;
    private ConnectedAppLoader $connectedAppLoader;
    private UserGroupLoader $groupLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getUserConsentedAuthenticationScopeQuery = $this->get(GetUserConsentedAuthenticationScopesQuery::class);
        $this->userConsentLoader = $this->get(UserConsentLoader::class);
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->groupLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_group_loader');
        $this->clock = $this->get(SystemClock::class);

        $this->clock->setNow(new \DateTimeImmutable('2021-03-02T04:30:11'));
    }

    public function test_it_gets_user_consented_scopes_from_the_database(): void
    {
        $scopes = ['a_scope', 'another_scope'];
        $uuid = Uuid::uuid4();
        $user = $this->authenticateAsAdmin();
        $this->connectionLoader->createConnection(
            'connectionCode',
            'Connector',
            FlowType::DATA_DESTINATION,
            false
        );
        $this->groupLoader->create(['name' => 'app_group']);
        $this->connectedAppLoader->createConnectedApp(
            'random_app_id',
            'Random App',
            ['scope'],
            'connectionCode',
            'http://www.example.com/path/to/logo/b',
            'autho',
            'app_group',
            ['category'],
            true,
            null
        );
        $this->userConsentLoader->addUserConsent(
            $user->getId(),
            'random_app_id',
            $scopes,
            $uuid,
            $this->clock->now()
        );

        $result = $this->getUserConsentedAuthenticationScopeQuery->execute($user->getId(), 'random_app_id');

        $this->assertEquals($scopes, $result);
    }

    public function test_it_gets_nothing_if_there_is_no_scopes_into_the_database(): void
    {
        $user = $this->authenticateAsAdmin();
        $result = $this->getUserConsentedAuthenticationScopeQuery->execute($user->getId(), 'random_app_id');

        $this->assertEquals([], $result);
    }
}
