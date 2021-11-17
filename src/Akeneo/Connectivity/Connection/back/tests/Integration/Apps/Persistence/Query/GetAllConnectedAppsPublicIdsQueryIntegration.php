<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence\Query;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnection;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\DbalConnectedAppRepository;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query\GetAllConnectedAppsPublicIdsQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllConnectedAppsPublicIdsQueryIntegration extends TestCase
{
    private GetAllConnectedAppsPublicIdsQuery $query;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get(DbalConnectedAppRepository::class);
        $this->query = $this->get(GetAllConnectedAppsPublicIdsQuery::class);
        $this->createConnection = $this->get(CreateConnection::class);
        $this->clientProvider = $this->get('akeneo_connectivity.connection.service.apps.client_provider');
        $this->createUserGroup = $this->get('akeneo_connectivity.connection.service.user.create_user_group');
        $this->createUser = $this->get('akeneo_connectivity.connection.service.user.create_user');
    }

    private function createConnectedApp(string $appPublicId): void
    {
        $group = $this->createUserGroup->execute('userGroup_' . $appPublicId);

        $user = $this->createUser->execute(
            'username_' . $appPublicId,
            'firstname_' . $appPublicId,
            'lastname_' . $appPublicId,
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
            'connectionCode_' . $appPublicId,
            'Connector_' . $appPublicId,
            FlowType::OTHER,
            $client->getId(),
            $user->id()
        );

        $this->repository->create(
            new ConnectedApp(
                'connectedAppId_' . $appPublicId,
                'App',
                [],
                'connectionCode_' . $appPublicId,
                'http://www.example.com/path/to/logo',
                'author',
                'userGroup_' . $appPublicId,
                [],
                false,
                'partner'
            )
        );
    }

    public function test_it_returns_nothing_when_no_connected_app_exists()
    {
        $result = $this->query->execute();

        $this->assertEmpty($result);
    }

    public function test_it_returns_connected_app_codes()
    {
        $this->createConnectedApp('foo');
        $this->createConnectedApp('bar');
        $this->createConnectedApp('baz');

        $result = $this->query->execute();

        $this->assertEqualsCanonicalizing(['foo', 'bar', 'baz'], $result);
    }
}
