<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\CreateUserConsentQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\FakeClock;
use Akeneo\Connectivity\Connection\Infrastructure\Service\Clock\SystemClock;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateUserConsentQueryIntegration extends WebTestCase
{
    private Connection $connection;
    private CreateUserConsentQuery $createUserConsentQuery;
    private FakeClock $clock;
    private ConnectionLoader $connectionLoader;
    private ConnectedAppLoader $connectedAppLoader;
    private UserGroupLoader $groupLoader;

    public function test_it_creates_user_consent_into_the_database(): void
    {
        $appId = 'random_app_id';
        $scopes = ['a_scope', 'another_scope'];
        $user = $this->authenticateAsAdmin();
        $this->connectionLoader->createConnection(
            'connectionCode',
            'Connector',
            FlowType::DATA_DESTINATION,
            false
        );
        $this->groupLoader->create(['name' => 'app_group']);
        $this->connectedAppLoader->createConnectedApp(
            $appId,
            'Random App',
            ['scope B1'],
            'connectionCode',
            'http://www.example.com/path/to/logo/b',
            'autho',
            'app_group',
            ['category B1'],
            true,
            null
        );

        $this->createUserConsentQuery->execute(
            $user->getId(),
            $appId,
            $scopes,
            $this->clock->now()
        );

        $result = $this->connection->executeQuery(
            "SELECT * FROM akeneo_connectivity_user_consent WHERE user_id=:userId AND app_id=:appId",
            [
                'userId' => $user->getId(),
                'appId' => $appId,
            ],
            [
                'userId' => Types::INTEGER,
                'appId' => Types::STRING,
            ]
        )->fetchAssociative();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('app_id', $result);
        $this->assertArrayHasKey('scopes', $result);
        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('consent_date', $result);
        $this->assertEquals($user->getId(), $result['user_id']);
        $this->assertEquals($appId, $result['app_id']);
        $this->assertEquals($scopes, \array_values(\json_decode($result['scopes'], null, 512, JSON_THROW_ON_ERROR)));
        $this->assertEquals(
            $this->clock->now()->format(\DateTimeInterface::ATOM),
            (new \DateTime($result['consent_date']))->format(\DateTimeInterface::ATOM)
        );
        $this->assertTrue(Uuid::isValid($result['uuid']));
    }

    public function test_it_creates_user_consent_with_empty_scopes_into_the_database(): void
    {
        $appId = 'random_app_id';
        $scopes = [];
        $user = $this->authenticateAsAdmin();
        $this->connectionLoader->createConnection(
            'connectionCode',
            'Connector',
            FlowType::DATA_DESTINATION,
            false
        );
        $this->groupLoader->create(['name' => 'app_group']);
        $this->connectedAppLoader->createConnectedApp(
            $appId,
            'Random App',
            ['scope B1'],
            'connectionCode',
            'http://www.example.com/path/to/logo/b',
            'autho',
            'app_group',
            ['category B1'],
            true,
            null
        );

        $this->createUserConsentQuery->execute(
            $user->getId(),
            $appId,
            $scopes,
            $this->clock->now()
        );

        $result = $this->connection->executeQuery(
            "SELECT * FROM akeneo_connectivity_user_consent WHERE user_id=:userId AND app_id=:appId",
            [
                'userId' => $user->getId(),
                'appId' => $appId,
            ],
            [
                'userId' => Types::INTEGER,
                'appId' => Types::STRING,
            ]
        )->fetchAssociative();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('app_id', $result);
        $this->assertArrayHasKey('scopes', $result);
        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('consent_date', $result);
        $this->assertEquals($user->getId(), $result['user_id']);
        $this->assertEquals($appId, $result['app_id']);
        $this->assertEquals($scopes, \array_values(\json_decode($result['scopes'], null, 512, JSON_THROW_ON_ERROR)));
        $this->assertEquals(
            $this->clock->now()->format(\DateTimeInterface::ATOM),
            (new \DateTime($result['consent_date']))->format(\DateTimeInterface::ATOM)
        );
        $this->assertTrue(Uuid::isValid($result['uuid']));
    }

    public function test_it_overrides_user_consent_into_the_database(): void
    {
        $appId = 'random_app_id';
        $scopes = ['a_scope', 'another_scope'];
        $user = $this->authenticateAsAdmin();
        $this->connectionLoader->createConnection(
            'connectionCode',
            'Connector',
            FlowType::DATA_DESTINATION,
            false
        );
        $this->groupLoader->create(['name' => 'app_group']);
        $this->connectedAppLoader->createConnectedApp(
            $appId,
            'Random App',
            ['scope B1'],
            'connectionCode',
            'http://www.example.com/path/to/logo/b',
            'autho',
            'app_group',
            ['category B1'],
            true,
            null
        );

        $this->createUserConsentQuery->execute(
            $user->getId(),
            $appId,
            $scopes,
            $this->clock->now()
        );


        $newScopes = ['a_new_scope', 'another_new_scope'];
        $this->clock->setNow(new \DateTimeImmutable('2021-03-03T04:30:11'));

        $this->createUserConsentQuery->execute(
            $user->getId(),
            $appId,
            $newScopes,
            $this->clock->now()
        );

        $result = $this->connection->executeQuery(
            "SELECT * FROM akeneo_connectivity_user_consent WHERE user_id=:userId AND app_id=:appId",
            [
                'userId' => $user->getId(),
                'appId' => $appId,
            ],
            [
                'userId' => Types::INTEGER,
                'appId' => Types::STRING,
            ]
        )->fetchAssociative();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('app_id', $result);
        $this->assertArrayHasKey('scopes', $result);
        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('consent_date', $result);
        $this->assertEquals($user->getId(), $result['user_id']);
        $this->assertEquals($appId, $result['app_id']);
        $this->assertEquals($newScopes, \array_values(\json_decode($result['scopes'], null, 512, JSON_THROW_ON_ERROR)));
        $this->assertEquals(
            $this->clock->now()->format(\DateTimeInterface::ATOM),
            (new \DateTime($result['consent_date']))->format(\DateTimeInterface::ATOM)
        );
        $this->assertTrue(Uuid::isValid($result['uuid']));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->groupLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_group_loader');
        $this->clock = $this->get(SystemClock::class);
        $this->createUserConsentQuery = $this->get(CreateUserConsentQuery::class);

        $this->clock->setNow(new \DateTimeImmutable('2021-03-02T04:30:11'));
    }
}
