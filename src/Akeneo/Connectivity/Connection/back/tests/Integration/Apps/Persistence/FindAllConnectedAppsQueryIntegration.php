<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\CreateConnectedAppQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindAllConnectedAppsQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAllConnectedAppsQueryIntegration extends TestCase
{
    private FindAllConnectedAppsQuery $query;
    private ConnectionLoader $connectionLoader;
    private UserGroupLoader $userGroupLoader;
    private CreateConnectedAppQuery $createConnectedAppQuery;
    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(FindAllConnectedAppsQuery::class);
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->userGroupLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_group_loader');
        $this->createConnectedAppQuery = $this->get(CreateConnectedAppQuery::class);
        $this->connection = $this->get('database_connection');
    }

    public function test_it_finds_all_ordered_by_name()
    {
        $this->connectionLoader->createConnection('connectionCodeB', 'Connector B', FlowType::DATA_DESTINATION, false);
        $this->userGroupLoader->create(['name' => 'app_7891011ghijkl']);
        $createdAppB = new ConnectedApp(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'App B',
            ['scope B1', 'scope B2'],
            'connectionCodeB',
            'http://www.example.com/path/to/logo/b',
            'author B',
            'app_7891011ghijkl',
            ['category B1'],
            true,
            null
        );
        $this->createConnectedAppQuery->execute($createdAppB);

        $this->connectionLoader->createConnection('connectionCodeA', 'Connector A', FlowType::DATA_DESTINATION, false);
        $this->userGroupLoader->create(['name' => 'app_123456abcdef']);
        $createdAppA = new ConnectedApp(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'App A',
            ['scope A1'],
            'connectionCodeA',
            'http://www.example.com/path/to/logo/a',
            'author A',
            'app_123456abcdef',
            ['category A1', 'category A2'],
            false,
            'partner A',
            true
        );
        $this->createConnectedAppQuery->execute($createdAppA);

        // created App A is a test app
        $this->connection->insert('akeneo_connectivity_test_app', [
            'client_id' => '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'client_secret' => 'secret',
            'name' => 'App A',
            'activate_url' => 'http://shopware.example.com/activate',
            'callback_url' => 'http://shopware.example.com/callback',
            'user_id' => null,
        ]);

        $connectedApps = $this->query->execute();

        Assert::assertEquals($createdAppA->normalize(), $connectedApps[0]->normalize());
        Assert::assertEquals($createdAppB->normalize(), $connectedApps[1]->normalize());
    }
}
