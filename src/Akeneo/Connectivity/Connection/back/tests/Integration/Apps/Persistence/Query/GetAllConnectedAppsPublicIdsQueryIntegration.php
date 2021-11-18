<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence\Query;

use Akeneo\Connectivity\Connection\Application\Apps\Service\CreateConnection;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\DbalConnectedAppRepository;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query\GetAllConnectedAppsPublicIdsQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllConnectedAppsPublicIdsQueryIntegration extends TestCase
{
    private ConnectedAppLoader $connectedAppLoader;
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
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->createConnection = $this->get(CreateConnection::class);
        $this->clientProvider = $this->get('akeneo_connectivity.connection.service.apps.client_provider');
        $this->createUserGroup = $this->get('akeneo_connectivity.connection.service.user.create_user_group');
        $this->createUser = $this->get('akeneo_connectivity.connection.service.user.create_user');
    }

    public function test_it_returns_nothing_when_no_connected_app_exists()
    {
        $result = $this->query->execute();

        $this->assertEmpty($result);
    }

    public function test_it_returns_connected_app_codes()
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'foo'
        );

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2777e764-f852-4956-bf9b-1a1ec1b0d146',
            'bar'
        );

        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2877e764-f852-4956-bf9b-1a1ec1b0d147',
            'baz'
        );

        $result = $this->query->execute();

        $this->assertEqualsCanonicalizing([
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            '2777e764-f852-4956-bf9b-1a1ec1b0d146',
            '2877e764-f852-4956-bf9b-1a1ec1b0d147'
        ], $result);
    }
}
